<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\JobspecRequirement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * How well one applicant matches one posting's actual requirements.
 *
 * WHAT THIS IS, AND WHAT IT IS NOT
 *
 * It is decision support. It reads the job specification, reads what the
 * applicant told us, and reports each requirement as met, not met, or unknown
 * with the evidence beside it. It is NOT a filter, NOT a gate, and NOT a
 * verdict: nothing here closes an application, changes a status, or removes
 * anybody from a list.
 *
 * THE THREE RULES
 *
 *   UNKNOWN IS NOT FAILURE. A requirement we cannot check against the data we
 *   hold returns 'unknown' and names the field that was missing. Treating
 *   silence as a failure would reject people for our own gaps in capture.
 *
 *   UNCLASSIFIED IS PREFERRED. A requirement HR has not marked mandatory is
 *   preferred, so a job specification nobody has configured can never cause a
 *   rejection.
 *
 *   NOTHING IS WEIGHTED. There is no authoritative weighting for these
 *   criteria anywhere, so there is no score. Counts are reported and ranking
 *   is lexicographic in a published order.
 *
 * WHAT IS SHOWN BUT NOT SCORED
 *
 * Competencies, technical competencies, other skills and computer skills are
 * prose on one side and either prose or broad categories on the other. They
 * are presented as evidence for HR to read and are never counted, because
 * comparing them mechanically would be the opaque guesswork this design
 * exists to avoid. Age, sex and employment status are never scored at all.
 */
class QualificationMatcher
{
    public const MET = 'met';
    public const NOT_MET = 'not_met';
    public const UNKNOWN = 'unknown';

    public const MANDATORY = 'mandatory';
    public const PREFERRED = 'preferred';

    /**
     * Match one application against the specification behind its posting.
     *
     * @return array{criteria:array,counts:array,unknowns:array,specification:array}
     */
    public static function forApplication(int $applicationId): array
    {
        $application = DB::connection('applicant')->table('tblapp_applications')
            ->where('id', $applicationId)
            ->first(['id', 'app_id', 'job_posting_id', 'request_position_id', 'applied_at']);

        if (!$application) {
            return self::nothing('That application could not be found.');
        }

        $spec = self::specificationFor($application);

        if (!$spec) {
            return self::nothing(
                'This posting has no job specification, so there is nothing to match against.'
            );
        }

        $importance = JobspecRequirement::importanceFor((int) $spec->jspec_id);
        $profile = self::profile((int) $application->app_id);

        $criteria = [];

        foreach (config('qualification_matching.criteria', []) as $key => $definition) {
            $criteria[] = self::evaluate($key, $definition, $spec, $profile, $importance);
        }

        return [
            'application_id' => (int) $application->id,
            'applied_at' => $application->applied_at,
            'specification' => [
                'jspec_id' => (int) $spec->jspec_id,
                /* Shown so HR knows what the match was made against, and so a
                   posting with an unconfigured specification is obvious. */
                'classified' => count($importance),
            ],
            'criteria' => $criteria,
            'counts' => self::counts($criteria),
            'unknowns' => collect($criteria)
                ->where('result', self::UNKNOWN)
                ->where('scored', true)
                ->pluck('missing')
                ->filter()
                ->values()
                ->all(),
        ];
    }

    /**
     * Rank several applications against one posting.
     *
     * Lexicographic, in the order published in config and printed on the
     * screen. There is no weighting anywhere in it, and the final key is a
     * deterministic tie-break so identical inputs always produce the same
     * order.
     */
    public static function rank(array $applicationIds): array
    {
        $rows = array_map(fn ($id) => self::forApplication((int) $id), $applicationIds);

        usort($rows, function ($a, $b) {
            $ca = $a['counts'] ?? [];
            $cb = $b['counts'] ?? [];

            /* Fewest unmet mandatory first: the one thing that genuinely
               separates candidates against a configured specification. */
            $cmp = ($ca['mandatory_unmet'] ?? 0) <=> ($cb['mandatory_unmet'] ?? 0);
            if ($cmp !== 0) { return $cmp; }

            $cmp = ($cb['mandatory_met'] ?? 0) <=> ($ca['mandatory_met'] ?? 0);
            if ($cmp !== 0) { return $cmp; }

            $cmp = ($cb['preferred_met'] ?? 0) <=> ($ca['preferred_met'] ?? 0);
            if ($cmp !== 0) { return $cmp; }

            /* Fewer unknowns ranks higher -- not because the applicant is
               better, but because there is more to go on. */
            $cmp = ($ca['unknown'] ?? 0) <=> ($cb['unknown'] ?? 0);
            if ($cmp !== 0) { return $cmp; }

            return strcmp((string) ($a['applied_at'] ?? ''), (string) ($b['applied_at'] ?? ''));
        });

        return $rows;
    }

    /** The rules, in words, for printing beside the list. */
    public static function rankingRules(): array
    {
        return [
            'Fewest unmet mandatory requirements',
            'Most mandatory requirements met',
            'Most preferred requirements met',
            'Fewest unknowns',
            'Applied earliest',
        ];
    }

    /* ================================================================== */

    /**
     * Evaluate one criterion.
     *
     * Every branch returns the evidence it used, and 'unknown' always names
     * what was missing — a result with no reason attached is not something
     * this should ever produce.
     */
    private static function evaluate(string $key, array $definition, object $spec, array $profile, array $importance): array
    {
        $required = self::items((string) ($spec->{$definition['jobspec_field']} ?? ''));

        $row = [
            'key' => $key,
            'label' => $definition['label'],
            'required' => $required,
            'importance' => $importance[$key] ?? self::PREFERRED,
            /* Said out loud so an unconfigured specification is visible
               rather than looking like a deliberate 'preferred'. */
            'classified' => array_key_exists($key, $importance),
            'scored' => (bool) ($definition['scored'] ?? false),
            'why_not_scored' => $definition['why_not_scored'] ?? null,
        ];

        /* Nothing asked for: nothing to fail. */
        if (empty($required)) {
            return $row + [
                'result' => self::MET,
                'evidence' => 'This posting asks for nothing here.',
                'missing' => null,
            ];
        }

        /* Shown, never counted. */
        if (!$row['scored']) {
            return $row + [
                'result' => self::UNKNOWN,
                'evidence' => $profile['prose'][$key] ?? 'Nothing recorded by the applicant.',
                'missing' => null,
            ];
        }

        return $row + match ($key) {
            'education' => self::education($required, $profile),
            'workexp' => self::experience($required, $profile),
            default => [
                'result' => self::UNKNOWN,
                'evidence' => 'No comparison is defined for this requirement.',
                'missing' => $definition['label'],
            ],
        };
    }

    /**
     * Education.
     *
     * Two things must both hold: the level reached, and that it was finished.
     * A Tertiary row still in progress does not satisfy "College Graduate",
     * which is why the status is read and not just the level.
     *
     * Three of the six requirement options have no representable answer in
     * applicant data at all. Those resolve to 'unknown' — never 'not met' —
     * because the system genuinely cannot tell.
     */
    private static function education(array $required, array $profile): array
    {
        $map = config('qualification_matching.education_levels', []);
        $rows = $profile['education'] ?? [];

        if (empty($rows)) {
            return [
                'result' => self::UNKNOWN,
                'evidence' => 'The applicant has recorded no education.',
                'missing' => 'Education history',
            ];
        }

        $best = 0;
        $bestLabel = '';

        foreach ($rows as $row) {
            if (!in_array($row['status'], config('qualification_matching.education_completed_statuses', []), true)) {
                continue;
            }

            $rank = config('qualification_matching.applicant_education_levels.' . $row['level'], 0);

            if ($rank > $best) {
                $best = $rank;
                $bestLabel = trim($row['level'] . ' — ' . ($row['degree'] ?: $row['school']));
            }
        }

        /* Everything they recorded is still in progress. */
        if ($best === 0) {
            $inProgress = collect($rows)->pluck('level')->unique()->implode(', ');

            return [
                'result' => self::UNKNOWN,
                'evidence' => 'Recorded but not completed: ' . $inProgress,
                'missing' => 'A completed qualification',
            ];
        }

        /*
        | The requirement list is a set of alternatives -- any one of them
        | satisfies it -- so the lowest ranked option that is expressible is
        | the bar. An option the applicant form cannot express is skipped
        | rather than counted against them.
        */
        $expressible = [];

        foreach ($required as $option) {
            $rank = $map[$option['value']] ?? null;

            if ($rank !== null) {
                $expressible[$option['value']] = $rank;
            }
        }

        if (empty($expressible)) {
            return [
                'result' => self::UNKNOWN,
                'evidence' => 'Holds ' . $bestLabel,
                'missing' => 'The applicant form cannot record '
                    . implode(' or ', array_column($required, 'value')),
            ];
        }

        $bar = min($expressible);

        if ($best >= $bar) {
            return [
                'result' => self::MET,
                'evidence' => 'Holds ' . $bestLabel,
                'missing' => null,
            ];
        }

        return [
            'result' => self::NOT_MET,
            'evidence' => 'Holds ' . $bestLabel . ', below '
                . implode(' / ', array_keys($expressible)),
            'missing' => null,
        ];
    }

    /**
     * Work experience.
     *
     * The requirement bands are ordered and the applicant's months are
     * computable, so this is the only criterion that compares cleanly.
     * Overlapping periods are merged, or two concurrent jobs would read as
     * twice the experience.
     */
    private static function experience(array $required, array $profile): array
    {
        $bands = config('qualification_matching.experience_bands', []);

        $needed = [];

        foreach ($required as $option) {
            if (array_key_exists($option['value'], $bands)) {
                $needed[$option['value']] = $bands[$option['value']];
            }
        }

        if (empty($needed)) {
            return [
                'result' => self::UNKNOWN,
                'evidence' => 'Asks for ' . implode(' / ', array_column($required, 'value')),
                'missing' => 'A recognised experience band',
            ];
        }

        $bar = min($needed);

        /* "Not Necessary (none)" is satisfied by anybody, including somebody
           with no employment history at all. */
        if ($bar === 0) {
            return [
                'result' => self::MET,
                'evidence' => 'No experience is required for this posting.',
                'missing' => null,
            ];
        }

        $periods = $profile['employment'] ?? [];

        if (empty($periods)) {
            return [
                'result' => self::UNKNOWN,
                'evidence' => 'The applicant has recorded no employment history.',
                'missing' => 'Employment history',
            ];
        }

        $months = self::monthsOfExperience($periods);
        $years = round($months / 12, 1);

        $evidence = $years . ' year' . ($years === 1.0 ? '' : 's')
            . ' recorded across ' . count($periods) . ' position' . (count($periods) === 1 ? '' : 's');

        if ($months >= $bar) {
            return ['result' => self::MET, 'evidence' => $evidence, 'missing' => null];
        }

        return [
            'result' => self::NOT_MET,
            'evidence' => $evidence . ', below ' . array_key_first($needed),
            'missing' => null,
        ];
    }

    /**
     * Total months of employment, with overlaps merged.
     *
     * Two jobs held at once are one period of experience, not two. Sorting by
     * start and extending a running window is the whole of it.
     */
    public static function monthsOfExperience(array $periods): int
    {
        $spans = [];

        foreach ($periods as $period) {
            if (empty($period['from'])) {
                continue;
            }

            $from = Carbon::parse($period['from'])->startOfMonth();

            /* A job they still hold runs to today. */
            $to = $period['current'] || empty($period['to'])
                ? Carbon::now()->startOfMonth()
                : Carbon::parse($period['to'])->startOfMonth();

            if ($to->lessThan($from)) {
                continue;
            }

            $spans[] = [$from, $to];
        }

        if (empty($spans)) {
            return 0;
        }

        usort($spans, fn ($a, $b) => $a[0] <=> $b[0]);

        $months = 0;
        [$start, $end] = $spans[0];

        foreach (array_slice($spans, 1) as [$from, $to]) {
            if ($from->lessThanOrEqualTo($end)) {
                /* Overlapping or touching: extend rather than add. */
                if ($to->greaterThan($end)) {
                    $end = $to;
                }
                continue;
            }

            $months += $start->diffInMonths($end);
            [$start, $end] = [$from, $to];
        }

        return $months + $start->diffInMonths($end);
    }

    /** The tallies, counting only what is scored. */
    private static function counts(array $criteria): array
    {
        $scored = collect($criteria)->where('scored', true);

        $of = fn ($importance, $result) => $scored
            ->where('importance', $importance)->where('result', $result)->count();

        return [
            'mandatory_met' => $of(self::MANDATORY, self::MET),
            'mandatory_unmet' => $of(self::MANDATORY, self::NOT_MET),
            'mandatory_unknown' => $of(self::MANDATORY, self::UNKNOWN),
            'mandatory_total' => $scored->where('importance', self::MANDATORY)->count(),
            'preferred_met' => $of(self::PREFERRED, self::MET),
            'preferred_unmet' => $of(self::PREFERRED, self::NOT_MET),
            'preferred_total' => $scored->where('importance', self::PREFERRED)->count(),
            'unknown' => $scored->where('result', self::UNKNOWN)->count(),
            /* Deliberately present and deliberately not a score. */
            'not_shown_as_score' => true,
        ];
    }

    /**
     * Split a stored multi-value field into its items.
     *
     * "%#" separates items; "%&" separates an item from its free-text detail,
     * and is used only by jspec_education. Both come from the legacy forms
     * (MP_JOBSPEC_DELIM), and parsing is kept identical to theirs.
     */
    private static function items(string $raw): array
    {
        $raw = trim($raw);

        if ($raw === '') {
            return [];
        }

        $itemDelimiter = config('qualification_matching.delimiters.item', '%#');
        $detailDelimiter = config('qualification_matching.delimiters.detail', '%&');

        $items = [];

        foreach (explode($itemDelimiter, $raw) as $chunk) {
            $chunk = trim($chunk);

            if ($chunk === '') {
                continue;
            }

            $parts = explode($detailDelimiter, $chunk, 2);

            $items[] = [
                'value' => trim($parts[0]),
                'detail' => isset($parts[1]) ? trim($parts[1]) : null,
            ];
        }

        return $items;
    }

    /** The specification behind this application's posting. */
    private static function specificationFor(object $application): ?object
    {
        $jspecId = null;

        if ($application->job_posting_id) {
            $jspecId = DB::table('tbl_job_posting')
                ->where('id', $application->job_posting_id)
                ->value('jobspec_id');
        }

        if (!$jspecId && $application->request_position_id) {
            $jspecId = DB::table('tbl_manpower_request_position')
                ->where('id', $application->request_position_id)
                ->value('jobspec_id');
        }

        if (!$jspecId) {
            return null;
        }

        return DB::table('tbl_manpower_jobspec')->where('jspec_id', $jspecId)->first();
    }

    /**
     * What the applicant told us.
     *
     * Read, never inferred. Where a section is empty it stays empty, and the
     * criteria that depend on it return 'unknown' rather than assuming.
     */
    private static function profile(int $appId): array
    {
        $c = DB::connection('applicant');

        $education = $c->table('tblapp_education')->where('app_id', $appId)
            ->get(['educ_level', 'educ_currStatus', 'educ_degreetitle', 'educ_major', 'educ_school'])
            ->map(fn ($row) => [
                'level' => (string) $row->educ_level,
                'status' => (string) $row->educ_currStatus,
                'degree' => trim((string) $row->educ_degreetitle . ' ' . (string) $row->educ_major),
                'school' => (string) $row->educ_school,
            ])->all();

        $employment = $c->table('tblapp_employment')->where('app_id', $appId)
            ->get(['empl_from', 'empl_to', 'empl_company', 'empl_position', 'empl_is_current'])
            ->map(fn ($row) => [
                'from' => $row->empl_from,
                'to' => $row->empl_to,
                'current' => (bool) $row->empl_is_current,
                'company' => (string) $row->empl_company,
                'position' => (string) $row->empl_position,
            ])->all();

        /* The unscored criteria, as evidence for a person to read. Skill
           categories are resolved to their names so the screen is readable. */
        $skills = $c->table('tblapp_skills as s')
            ->leftJoin(DB::connection('mysql')->getDatabaseName() . '.tbl_skill_type as t', 't.id', '=', 's.skill_type')
            ->where('s.app_id', $appId)
            ->get(['t.skill_name', 's.skill_others'])
            ->map(fn ($row) => trim((string) ($row->skill_name ?: $row->skill_others)))
            ->filter()->implode(', ');

        $software = $c->table('tblapp_skill_software')->where('app_id', $appId)
            ->pluck('appss_content')->filter()->implode('; ');

        $certificates = $c->table('tblapp_certificate')->where('app_id', $appId)
            ->pluck('cert_title')->filter()->implode(', ');

        return [
            'education' => $education,
            'employment' => $employment,
            'prose' => [
                'competencies' => $skills ?: 'No skills recorded by the applicant.',
                'techcompetencies' => $skills ?: 'No skills recorded by the applicant.',
                'otherskill' => $skills ?: 'No skills recorded by the applicant.',
                'computerskill' => $software ?: 'No software skills recorded by the applicant.',
                'certification' => $certificates ?: 'No certificates recorded by the applicant.',
            ],
        ];
    }

    /** When there is nothing to match, said plainly rather than scored as zero. */
    private static function nothing(string $why): array
    {
        return [
            'application_id' => null,
            'applied_at' => null,
            'specification' => null,
            'criteria' => [],
            'counts' => self::counts([]),
            'unknowns' => [],
            'unavailable' => $why,
        ];
    }
}

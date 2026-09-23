<?php

namespace App\Services\Recruitment\Ad;

use App\Models\Recruitment\HireflowManpowerPosition;
use App\Services\Recruitment\JobSpec\SpecFacts;
use Illuminate\Support\Facades\DB;

/**
 * The short job description: two or three plain sentences about a posting,
 * for anywhere the full ad does not fit — a job board's summary field, a
 * social post, the careers list, a link preview.
 *
 * Drafted from the same publication-safe facts as the public ad (SpecFacts:
 * no age, sex, headcount or assessment fields), plus the job title's own
 * summary in tbl_jobdescription when HR has written one. Plain text only — no
 * emoji or line breaks — so it pastes cleanly into any platform. HR edits it
 * freely; the draft is only a starting point.
 */
class JobShortDescription
{
    /** Matches tbl_job_posting.short_description. */
    public const MAX = 500;

    private const DUTIES_SHOWN = 3;

    /** A draft aims for about this length; duties stop being added past it. */
    private const TARGET = 300;

    public function composeFor(HireflowManpowerPosition $position): string
    {
        $spec = $position->jobSpec();
        $facts = (new JobAdComposer())->extractor()->extract($spec, $position->positionTitle());

        $summary = DB::connection('hrd2')->table('tbl_jobdescription')
            ->where('jd_code', $spec->jspec_position ?? $position->position)
            ->value('jd_summary');

        return $this->compose($facts, $summary);
    }

    public function compose(SpecFacts $facts, ?string $summary = null): string
    {
        $lead = $facts->title . ($facts->departmentName ? ' — ' . $facts->departmentName : '') . '.';

        $employment = HireflowManpowerPosition::employmentCopyFor($facts->employmentStatus)['label'];
        $where = lcfirst(HireflowManpowerPosition::AD_LOCATION);
        $terms = $employment ? $employment . ', ' . $where . '.' : ucfirst($where) . '.';

        $body = $this->clean((string) $summary);

        if ($body === '' && $facts->duties) {
            $opening = "$lead $terms Key duties: ";
            $duties = [];
            foreach (array_slice($facts->duties, 0, self::DUTIES_SHOWN) as $duty) {
                // A duty written as a paragraph contributes its first sentence
                // (a full stop after a real word, so "Jr." is not an ending).
                $duty = rtrim(preg_split('/(?<=\p{Ll}{3}[.!?])\s+(?=\p{Lu})/u', $this->clean($duty))[0], '.;: ');
                $duty = $this->shorten($duty, 160);
                if ($duty === '' || ($duties && mb_strlen($opening . implode('; ', [...$duties, $duty])) > self::TARGET)) {
                    break;
                }
                $duties[] = $duty;
            }
            $body = $duties ? 'Key duties: ' . implode('; ', $duties) . (str_ends_with(end($duties), '…') ? '' : '.') : '';
        } elseif ($body !== '' && !preg_match('/[.!?]$/u', $body)) {
            $body .= '.';
        }

        return $this->shorten(trim("$lead $terms $body"), self::MAX);
    }

    /** Cut at a word boundary, marked with an ellipsis, within $max characters. */
    private function shorten(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space ? mb_substr($cut, 0, $space) : $cut, ' ,;:(') . '…';
    }

    /** One line of plain text; SHOUTED text is brought down to sentence case. */
    private function clean(string $text): string
    {
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text));

        if ($text !== '' && preg_match('/\p{L}/u', $text) && mb_strtoupper($text) === $text) {
            $text = mb_strtoupper(mb_substr($text, 0, 1)) . mb_strtolower(mb_substr($text, 1));
        }

        return $text;
    }
}

<?php

namespace App\Services\Recruitment\JobSpec;

use Illuminate\Support\Facades\DB;

/**
 * Turns a raw tbl_manpower_jobspec row into SpecFacts.
 *
 * Splitting mirrors how HireFlow actually stores each field
 * (zen/manpower/public/jobspec_save.php):
 *   - checkbox groups  → joined with "%#"      → 'delimiter'
 *   - prose textareas  → stored raw            → 'lines' / 'lines_commas'
 *
 * Values that are only digits and punctuation are dropped: HireFlow's duties
 * box is free text, so a bare "77" is placeholder input, not a duty, and must
 * never be published as one.
 */
class SpecFactExtractor
{

    /** @var array<string,string|null> resolved lookup caches */
    private static array $departmentCache = [];
    private static array $sectionCache = [];

    public function __construct(private array $config)
    {
    }

    public function extract(?object $spec, string $title): SpecFacts
    {
        $policy = $this->config['fields'] ?? [];

        $raw = ['title' => $title];
        foreach (array_keys($policy) as $field) {
            $raw[$field] = (string) ($spec->{$field} ?? '');
        }

        $education = $this->items($spec, 'jspec_education', $policy);
        $workexp   = $this->items($spec, 'jspec_workexp', $policy);

        $departmentName = $this->config['show_department'] ?? false
            ? $this->lookupDepartment($spec->jspec_department ?? null)
            : null;

        $sectionName = $this->config['show_section'] ?? false
            ? $this->lookupSection($spec->jspec_section ?? null)
            : null;

        if ($departmentName !== null) {
            $raw['department_name'] = $departmentName;
        }
        if ($sectionName !== null) {
            $raw['section_name'] = $sectionName;
        }

        return new SpecFacts(
            title: $title,
            departmentName: $departmentName,
            sectionName: $sectionName,
            employmentStatus: $this->blankToNull((string) ($spec->jspec_emplstat ?? '')),
            education: $education,
            workexp: $workexp,
            experienceNotRequired: $this->experienceNotRequired($workexp),
            duties: $this->items($spec, 'jspec_duties', $policy),
            techCompetencies: $this->items($spec, 'jspec_techcompetencies', $policy),
            competencies: $this->items($spec, 'jspec_competencies', $policy),
            computerSkills: $this->items($spec, 'jspec_computerskill', $policy),
            otherSkills: $this->items($spec, 'jspec_otherskill', $policy),
            raw: $raw,
            excluded: $this->excludedValues($spec),
        );
    }

    /* ---------------------------------------------------------------- */

    private function items(?object $spec, string $field, array $policy): array
    {
        $rule = $policy[$field] ?? ['split' => 'lines', 'max' => 8];
        $raw  = trim((string) ($spec->{$field} ?? ''));

        if ($raw === '') {
            return [];
        }

        // A stray entry delimiter in a prose field is still an entry break —
        // treat it as a line break so it can never reach the rendered ad.
        if ($rule['split'] !== 'delimiter') {
            $raw = str_replace($this->entryDelimiter(), "\n", $raw);
        }

        $pieces = match ($rule['split']) {
            'delimiter'    => explode($this->entryDelimiter(), $raw),
            'lines_commas' => $this->splitLinesAndCommas($raw),
            default        => $this->splitLines($raw),
        };

        $out = [];
        foreach ($pieces as $piece) {
            $clean = $this->normaliseEntry((string) $piece);
            if ($clean !== null) {
                $out[] = $clean;
            }
        }

        return array_slice(array_values(array_unique($out)), 0, $rule['max'] ?? 8);
    }

    private function splitLines(string $raw): array
    {
        return preg_split('/\r\n|\r|\n|[;•·]/u', $raw) ?: [];
    }

    private function splitLinesAndCommas(string $raw): array
    {
        $out = [];
        foreach ($this->splitLines($raw) as $line) {
            foreach ($this->splitOutsideBrackets((string) $line) as $piece) {
                $out[] = $piece;
            }
        }

        return $out;
    }

    /**
     * Commas only break an item at bracket depth zero, so a grouped list such
     * as "MS Office (Word, Excel, Visio)" survives as one skill.
     */
    private function splitOutsideBrackets(string $text): array
    {
        $pieces = [];
        $buffer = '';
        $depth  = 0;

        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
            if ($char === '(' || $char === '[') {
                $depth++;
            } elseif ($char === ')' || $char === ']') {
                $depth = max(0, $depth - 1);
            }

            if ($char === ',' && $depth === 0) {
                $pieces[] = $buffer;
                $buffer = '';
                continue;
            }

            $buffer .= $char;
        }

        $pieces[] = $buffer;

        return $pieces;
    }

    private function entryDelimiter(): string
    {
        return $this->config['entry_delimiter'] ?? '%#';
    }

    private function detailDelimiter(): string
    {
        return $this->config['detail_delimiter'] ?? '%&';
    }

    private function detailSeparator(): string
    {
        return $this->config['detail_separator'] ?? ' — ';
    }

    /**
     * One stored entry becomes one readable item.
     *
     * HireFlow joins a selected option to its free-text detail with "%&", e.g.
     * "College Graduate (4 year course)%&Any IT Technical Course". Both halves
     * are legitimate information supplied by HR, so neither is dropped: they
     * are cleaned separately and rejoined with a readable separator. Splitting
     * before cleanItem() also matters because cleanItem() strips HireFlow's
     * "**Specify" marker to end-of-string, which would otherwise swallow the
     * detail of "Masterate / Doctoral**Specify%&Master in Information Systems".
     */
    private function normaliseEntry(string $entry): ?string
    {
        $parts = [];

        foreach (explode($this->detailDelimiter(), $entry) as $part) {
            $clean = $this->cleanItem($part);
            if ($clean !== null) {
                $parts[] = $clean;
            }
        }

        if ($parts === []) {
            return null;
        }

        return implode($this->detailSeparator(), array_values(array_unique($parts)));
    }

    private function cleanItem(string $piece): ?string
    {
        // HireFlow marks "specify" options like "Masterate / Doctoral**Specify"
        $piece = preg_replace('/\*\*.*$/u', '', $piece) ?? $piece;

        // List markers only ("- ", "* ", "1. ", "2) ") — never a bare leading
        // digit, which is content ("1 to 2 years").
        $piece = preg_replace('/^\s*(?:[-*+]|\d+\s*[.)])\s+/u', '', $piece) ?? $piece;
        $piece = trim($piece, " \t\r\n-–—:");

        if ($piece === '') {
            return null;
        }

        if (preg_match('/^(none|n\/?a|etc\.?\)?)$/i', $piece)) {
            return null;
        }

        // Placeholder input: digits and punctuation only, e.g. "77", "23".
        if (preg_match('/^[\d\s,.\/-]+$/u', $piece)) {
            return null;
        }

        return $piece;
    }

    private function experienceNotRequired(array $workexp): bool
    {
        if ($workexp === []) {
            return false;
        }

        foreach ($workexp as $item) {
            if (preg_match('/^(not\s+necessary|not\s+required|none|n\/?a)\b/i', $item)) {
                return true;
            }
        }

        return false;
    }

    private function excludedValues(?object $spec): array
    {
        $out = [];
        foreach ($this->config['excluded_fields'] ?? [] as $field) {
            $value = trim((string) ($spec->{$field} ?? ''));
            if ($value !== '') {
                $out[$field] = $value;
            }
        }

        return $out;
    }

    private function blankToNull(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /* ---------------- HireFlow lookups (hrd2 connection) -------------- */

    private function lookupDepartment(?string $code): ?string
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        if (!array_key_exists($code, self::$departmentCache)) {
            self::$departmentCache[$code] = DB::connection('hrd2')
                ->table('tbl_department')
                ->where('Dept_Code', $code)
                ->value('Dept_Name') ?: null;
        }

        return self::$departmentCache[$code];
    }

    private function lookupSection(?string $code): ?string
    {
        $code = trim((string) $code);
        if ($code === '') {
            return null;
        }

        if (!array_key_exists($code, self::$sectionCache)) {
            self::$sectionCache[$code] = DB::connection('hrd2')
                ->table('tbl_section')
                ->where('sec_code', $code)
                ->value('sec_name') ?: null;
        }

        return self::$sectionCache[$code];
    }
}

<?php

namespace App\Services\Recruitment\Ad;

/**
 * Resolves which public-ad template a Job Spec earns, and can say why.
 *
 * The signals already existed — AdContentProfile computes a tier from how
 * much the spec contains and a primary tag from the keyword sets in
 * config/job_ad.php. This turns them into a choice between the templates in
 * config/job_ad_templates.php, instead of one section order hard-coded in the
 * planner.
 *
 * Reads only; picking a template never changes a stored ad, and a
 * hand-written ad (ad_is_custom) is never recomposed at all.
 */
class AdTemplateRegistry
{
    private array $templates;
    private string $defaultKey;

    public function __construct(?array $config = null)
    {
        $config = $config ?? config('job_ad_templates', []);
        $this->templates = $config['templates'] ?? [];
        $this->defaultKey = $config['default'] ?? 'detailed';
    }

    /** @return array<string,array> every template, for listing in the UI */
    public function all(): array
    {
        return $this->templates;
    }

    public function get(string $key): ?array
    {
        return $this->templates[$key] ?? null;
    }

    /**
     * The template this profile earns.
     *
     * Templates are tried in 'priority' order, lowest first, so the most
     * specific rule gets first refusal. The default is returned when nothing
     * matches, which is why it must not carry a restrictive rule.
     *
     * @return array{key:string,template:array,reason:string}
     */
    public function resolve(AdContentProfile $profile, ?string $force = null): array
    {
        $tier = $profile->tier();
        $tag = $profile->primaryTag();

        // HR picked a template explicitly; the rule only recommends.
        if ($force !== null && isset($this->templates[$force])) {
            return [
                'key' => $force,
                'template' => $this->templates[$force],
                'reason' => 'Chosen by you.',
                'forced' => true,
            ];
        }

        $ordered = $this->templates;
        uasort($ordered, fn ($a, $b) => ($a['priority'] ?? 50) <=> ($b['priority'] ?? 50));

        foreach ($ordered as $key => $template) {
            if ($this->matches($template['when'] ?? [], $tier, $tag)) {
                return [
                    'key' => $key,
                    'template' => $template,
                    'reason' => $this->explain($template['when'] ?? [], $tier, $tag),
                ];
            }
        }

        $default = $this->templates[$this->defaultKey] ?? reset($this->templates);
        $defaultKey = isset($this->templates[$this->defaultKey])
            ? $this->defaultKey
            : (string) array_key_first($this->templates);

        return [
            'key' => $defaultKey,
            'template' => $default,
            'reason' => $this->matches($default['when'] ?? [], $tier, $tag)
                ? $this->explain($default['when'] ?? [], $tier, $tag)
                : 'No other template fitted this Job Specification, so the standard layout was used.',
        ];
    }

    /**
     * 'match' => 'any' passes when either listed signal hits; otherwise every
     * condition that the rule states must hold. An empty list is "don't care",
     * never "match nothing".
     */
    private function matches(array $when, string $tier, ?string $tag): bool
    {
        $tiers = $when['tiers'] ?? [];
        $tags = $when['tags'] ?? [];

        $tierHit = empty($tiers) || in_array($tier, $tiers, true);
        $tagHit = empty($tags) || ($tag !== null && in_array($tag, $tags, true));

        if (($when['match'] ?? 'all') === 'any') {
            // "Don't care" must not count as a hit, or every rule passes.
            return (!empty($tiers) && in_array($tier, $tiers, true))
                || (!empty($tags) && $tag !== null && in_array($tag, $tags, true));
        }

        return $tierHit && $tagHit;
    }

    /** Plain English for the panel, so HR is not guessing. */
    private function explain(array $when, string $tier, ?string $tag): string
    {
        $reasons = [];

        if (!empty($when['tiers']) && in_array($tier, $when['tiers'], true)) {
            $reasons[] = match ($tier) {
                AdContentProfile::TIER_RICH => 'the Job Specification has plenty of detail',
                AdContentProfile::TIER_PARTIAL => 'the Job Specification has some detail',
                default => 'the Job Specification has little detail',
            };
        }

        if (!empty($when['tags']) && $tag !== null && in_array($tag, $when['tags'], true)) {
            $reasons[] = 'it reads as a ' . str_replace('_', '/', $tag) . ' role';
        }

        return $reasons === []
            ? 'Selected as the standard layout.'
            : 'Chosen because ' . implode(' and ', $reasons) . '.';
    }
}

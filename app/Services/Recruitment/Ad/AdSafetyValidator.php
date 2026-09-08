<?php

namespace App\Services\Recruitment\Ad;

use App\Exceptions\AdCompositionException;
use App\Services\Recruitment\JobSpec\SpecFacts;

/**
 * Proves a composed ad invented nothing.
 *
 * Three assertions, run before the ad is ever returned:
 *
 *   1. Every SOURCE block's words are a subset of the words in the Job Spec
 *      column it claims to come from. Both sides go through the same
 *      canonicaliser, so an approved typo fix or spacing change cannot be used
 *      to slip in a new word.
 *   2. Every CONTROLLED and CONNECTIVE block matches the approved registry
 *      exactly — no interpolation, therefore no job facts.
 *   3. No excluded Job Spec value (age range, sex, headcount, assessment
 *      fields) appears anywhere in the output.
 *   4. No HireFlow storage delimiter ("%#", "%&") survives into the output.
 *      Note that assertion 1 cannot catch these on its own: the canonicaliser
 *      reduces punctuation to spaces, so a leaked delimiter still passes the
 *      token-subset test. This is a separate, literal check.
 *
 * A failure throws; JobAdComposer catches it and falls back to a minimal ad.
 * Unverified copy never reaches the database.
 */
class AdSafetyValidator
{
    private array $approved;

    public function __construct(private array $config)
    {
        $this->approved = $this->approvedStrings();
    }

    /**
     * @param AdBlock[] $blocks
     * @throws AdCompositionException
     */
    public function assertSafe(array $blocks, SpecFacts $facts): void
    {
        $filler = array_map(
            fn ($t) => mb_strtolower($t),
            $this->config['filler_tokens'] ?? []
        );

        foreach ($blocks as $block) {
            if ($block->isBlank()) {
                continue;
            }

            if ($block->type === AdBlock::SOURCE) {
                $this->assertTracesToSource($block, $facts, $filler);
                continue;
            }

            if ($block->text !== '' && !in_array($block->text, $this->approved, true)) {
                throw new AdCompositionException(sprintf(
                    'Unapproved %s copy: "%s"',
                    $block->type,
                    $block->text
                ));
            }
        }

        $this->assertNoExcludedLeak($blocks, $facts);
        $this->assertNoStorageDelimiters($blocks);
    }

    /* ---------------------------------------------------------------- */

    private function assertTracesToSource(AdBlock $block, SpecFacts $facts, array $filler): void
    {
        $origin = $block->origin ?? '';
        $raw    = $facts->raw[$origin] ?? null;

        if ($raw === null) {
            throw new AdCompositionException(
                "Source block claims unknown origin field '{$origin}'."
            );
        }

        $blockTokens  = $this->tokens($block->text);
        $originTokens = $this->tokens($raw);

        foreach ($blockTokens as $token) {
            if (in_array($token, $filler, true)) {
                continue;
            }

            if (!in_array($token, $originTokens, true)) {
                throw new AdCompositionException(sprintf(
                    'Word "%s" in "%s" does not appear in %s.',
                    $token,
                    $block->text,
                    $origin
                ));
            }
        }
    }

    private function assertNoExcludedLeak(array $blocks, SpecFacts $facts): void
    {
        $rendered = ' ' . $this->canonicalise(
            implode(' ', array_map(fn (AdBlock $b) => $b->render(), $blocks))
        ) . ' ';

        foreach ($facts->excluded as $field => $value) {
            foreach (explode('%#', $value) as $part) {
                $needle = trim($this->canonicalise($part));

                // Single short tokens ("Both", "Low") are ordinary words and
                // would false-positive; only guard distinctive phrases.
                if (mb_strlen($needle) < 6 || !str_contains($needle, ' ')) {
                    continue;
                }

                if (str_contains($rendered, ' ' . $needle . ' ')) {
                    throw new AdCompositionException(
                        "Excluded field {$field} leaked into the ad: \"{$part}\"."
                    );
                }
            }
        }
    }

    /**
     * Storage syntax must never be published. The extractor already resolves
     * every known delimiter, so this firing means an unhandled storage format
     * reached the renderer — fail to the minimal ad rather than print it.
     */
    private function assertNoStorageDelimiters(array $blocks): void
    {
        $delimiters = array_filter([
            $this->config['entry_delimiter'] ?? '%#',
            $this->config['detail_delimiter'] ?? '%&',
        ]);

        foreach ($blocks as $block) {
            $rendered = $block->render();

            foreach ($delimiters as $delimiter) {
                if (str_contains($rendered, $delimiter)) {
                    throw new AdCompositionException(sprintf(
                        'Storage delimiter "%s" leaked into the ad: "%s"',
                        $delimiter,
                        $rendered
                    ));
                }
            }
        }
    }

    /** Lowercase, apply approved fixes, reduce punctuation to spaces. */
    private function canonicalise(string $text): string
    {
        $text = mb_strtolower($text);

        foreach ($this->config['typo_map'] ?? [] as $from => $to) {
            $text = preg_replace(self::typoPattern($from), mb_strtolower($to), $text) ?? $text;
        }

        $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /**
     * Word-bounded for plain-word keys so a fix cannot be applied twice
     * ("Acces" must not also match inside the already-correct "Access").
     */
    public static function typoPattern(string $from): string
    {
        return preg_match('/^[\p{L}\p{N} ]+$/u', $from)
            ? '/\b' . preg_quote($from, '/') . '\b/iu'
            : '/' . preg_quote($from, '/') . '/iu';
    }

    /** @return string[] */
    private function tokens(string $text): array
    {
        $canonical = $this->canonicalise($text);

        return $canonical === '' ? [] : explode(' ', $canonical);
    }

    /** Every string the renderer is allowed to emit as non-source copy. */
    private function approvedStrings(): array
    {
        $strings = array_values($this->config['connectives'] ?? []);
        $strings[] = $this->config['apply_line'] ?? '';
        $strings[] = $this->config['neutral_hook'] ?? '';

        foreach ($this->config['tags'] ?? [] as $tag) {
            $strings[] = $tag['hook'] ?? '';
        }

        foreach ($this->config['runtime_approved'] ?? [] as $extra) {
            $strings[] = $extra;
        }

        return array_values(array_filter($strings, fn ($s) => $s !== ''));
    }
}

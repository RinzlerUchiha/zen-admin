<?php

namespace App\Services\Recruitment\Ad;

/**
 * One line of a public ad, carrying its provenance.
 *
 * SOURCE     — text derived from a named Job Spec column. The validator proves
 *              every word traces back to that column.
 * CONTROLLED — approved company copy (benefits, hooks, location, apply line).
 *              Must match an entry in the controlled registry exactly.
 * CONNECTIVE — readability glue. Selected by key from a fixed registry and
 *              never interpolated, so it cannot contain a job fact.
 *
 * `prefix` holds decoration (emoji, bullet) and is excluded from validation;
 * only `text` is checked against the source.
 */
final class AdBlock
{
    public const SOURCE = 'source';
    public const CONTROLLED = 'controlled';
    public const CONNECTIVE = 'connective';

    private function __construct(
        public readonly string $type,
        public readonly string $text,
        public readonly string $prefix = '',
        public readonly ?string $origin = null,
    ) {
    }

    public static function source(string $text, string $origin, string $prefix = ''): self
    {
        return new self(self::SOURCE, $text, $prefix, $origin);
    }

    public static function controlled(string $text, string $prefix = ''): self
    {
        return new self(self::CONTROLLED, $text, $prefix);
    }

    public static function connective(string $text): self
    {
        return new self(self::CONNECTIVE, $text);
    }

    /** A spacer. Renders as an empty line and is exempt from validation. */
    public static function blank(): self
    {
        return new self(self::CONTROLLED, '');
    }

    public function isBlank(): bool
    {
        return $this->text === '' && $this->prefix === '';
    }

    public function render(): string
    {
        return $this->prefix . $this->text;
    }
}

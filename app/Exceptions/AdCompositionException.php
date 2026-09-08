<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a composed public ad fails the safety validator — a line that
 * does not trace back to the Job Spec, unapproved company copy, or an
 * excluded field leaking into the output.
 *
 * JobAdComposer catches this, logs it, and falls back to a minimal ad, so a
 * validation failure degrades the ad rather than publishing unverified copy.
 */
class AdCompositionException extends RuntimeException
{
}

<?php

namespace Tests\Unit;

use App\Http\Controllers\ManpowerRequestController;
use ReflectionMethod;
use Tests\TestCase;

class ManpowerRequestControllerStatusTest extends TestCase
{
    private function resolveStatus(?string $submitMode, ?string $currentStatus): string
    {
        $method = new ReflectionMethod(ManpowerRequestController::class, 'resolveRequestStatus');
        $method->setAccessible(true);

        return $method->invoke(null, $submitMode, $currentStatus);
    }

    public function test_draft_submission_defaults_to_draft_status(): void
    {
        $this->assertSame('draft', $this->resolveStatus('draft', null));
    }

    public function test_pending_submission_uses_pending_status(): void
    {
        $this->assertSame('pending', $this->resolveStatus('pending', null));
    }

    public function test_existing_non_draft_requests_keep_their_current_status(): void
    {
        $this->assertSame('approved', $this->resolveStatus('draft', 'approved'));
    }
}

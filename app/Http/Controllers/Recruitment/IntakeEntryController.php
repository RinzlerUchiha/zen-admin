<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\IntakeEntry;
use App\Services\Recruitment\IntakeEntryPoints;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * HR's controls for the office kiosk and job-fair events.
 *
 * Activating one hands back a URL and the string a QR encodes; the QR itself is
 * drawn in the browser from a vendored library, so no image is generated or
 * stored anywhere.
 *
 * Source tracking only. Nothing here touches stage, interviews, exams,
 * documents, onboarding or whether anybody is hired.
 */
class IntakeEntryController extends Controller
{
    /** Everything the panel shows, in one call. */
    public function index()
    {
        $walkIn = IntakeEntryPoints::activeWalkIn();
        $activeFairs = IntakeEntryPoints::activeJobFairs();
        $pastFairs = IntakeEntryPoints::pastJobFairs();

        $counts = IntakeEntryPoints::applicationCounts();

        return response()->json([
            'links_ready' => config('posting_channels.careers_url') !== '',
            'walk_in' => $walkIn ? $this->present($walkIn, $counts) : null,
            'active_fairs' => $activeFairs->map(fn ($e) => $this->present($e, $counts))->values(),
            'past_fairs' => $pastFairs->map(fn ($e) => $this->present($e, $counts))->values(),
        ]);
    }

    public function activateWalkIn()
    {
        $entry = IntakeEntryPoints::activateWalkIn(Auth::user()->Emp_No ?? null);

        return response()->json([
            'success' => true,
            'entry' => $this->present($entry, IntakeEntryPoints::applicationCounts([$entry->id])),
            'message' => 'The office kiosk is live. Bookmark the link on that machine.',
        ]);
    }

    public function activateJobFair(Request $request)
    {
        $validated = $request->validate([
            'label' => 'required|string|max:150',
            'event_date' => 'nullable|date',
        ], [
            'label.required' => 'Give the event a name, so its applications can be told from another fair\'s.',
        ]);

        $entry = IntakeEntryPoints::activateJobFair($validated, Auth::user()->Emp_No ?? null);

        return response()->json([
            'success' => true,
            'entry' => $this->present($entry, []),
            'message' => 'Event is live. Its QR code and link are ready.',
        ]);
    }

    public function deactivate(int $id)
    {
        $entry = IntakeEntryPoints::deactivate($id, Auth::user()->Emp_No ?? null);

        return response()->json([
            'success' => true,
            'entry' => $this->present($entry, IntakeEntryPoints::applicationCounts([$entry->id])),
            // Said plainly, because "deactivate" could be read as "delete".
            'message' => 'Closed. The link and QR no longer work, and the '
                . 'applications already recorded against it are kept.',
        ]);
    }

    /** Reopening issues a NEW token, so old printed codes stay dead. */
    public function reactivate(int $id)
    {
        $entry = IntakeEntryPoints::reactivate($id, Auth::user()->Emp_No ?? null);

        return response()->json([
            'success' => true,
            'entry' => $this->present($entry, IntakeEntryPoints::applicationCounts([$entry->id])),
            'message' => 'Reopened with a new link. Any QR printed for the earlier run will not work.',
        ]);
    }

    /**
     * One entry point as the panel needs it.
     *
     * The URL is included because the panel shows it and the QR encodes it.
     * The raw token is not sent separately — there is no use for it beyond the
     * URL, and fewer copies of a secret is better.
     */
    private function present(IntakeEntry $entry, array $counts): array
    {
        return [
            'id' => $entry->id,
            'kind' => $entry->kind,
            'name' => $entry->displayName(),
            'label' => $entry->label,
            'event_date' => $entry->event_date?->toDateString(),
            'active' => $entry->isActive(),
            'url' => IntakeEntryPoints::entryUrl($entry),
            'activated_by' => $entry->activated_by,
            'activated_at' => $entry->activated_at?->toDateTimeString(),
            'deactivated_at' => $entry->deactivated_at?->toDateTimeString(),
            'applications' => (int) ($counts[$entry->id] ?? 0),
        ];
    }
}

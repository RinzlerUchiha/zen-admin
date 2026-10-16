<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\JobspecRequirement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Which of a job specification's requirements are mandatory.
 *
 * WHY THIS SCREEN EXISTS
 *
 * The legacy form asks for "EDUCATIONAL ATTAINMENT REQUIRED/PREFERRED" in one
 * checkbox list, so required and preferred are conflated in the stored data and
 * no amount of reading it can separate them. The distinction was never
 * captured, and this is where it is captured now.
 *
 * WHY IT LIVES HERE AND NOT IN THE LEGACY FORM
 *
 * tbl_manpower_jobspec is written by two legacy forms in two applications.
 * Teaching both of them a new column would mean changing both to maintain
 * something neither has a concept of. The classification is a separate table,
 * and this is the one screen that writes it.
 *
 * ABSENCE MEANS PREFERRED
 *
 * An unclassified criterion is preferred, so a specification nobody has been
 * through can never cause a rejection. That is why this screen is safe to
 * leave unused, and why it does not need a migration to populate anything.
 */
class JobSpecRequirementController extends Controller
{
    public function index($jspec)
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'requirements',
            'maincat' => 'requirements',
            'page' => 'pages.recruitment.requirements.index-content',
            'jspecId' => (int) $jspec,
        ]);
    }

    /**
     * The criteria, what the specification asks for, and how each is classified.
     *
     * The unscored criteria are included and marked: HR should see that
     * classifying them changes nothing, rather than wonder why their choice
     * had no effect.
     */
    public function data($jspec)
    {
        $jspec = (int) $jspec;

        $spec = DB::table('tbl_manpower_jobspec')->where('jspec_id', $jspec)->first();

        if (!$spec) {
            return response()->json(['message' => 'That job specification could not be found.'], 404);
        }

        $importance = JobspecRequirement::importanceFor($jspec);
        $delimiter = config('qualification_matching.delimiters.item', '%#');

        $criteria = [];

        foreach (config('qualification_matching.criteria', []) as $key => $definition) {
            $raw = trim((string) ($spec->{$definition['jobspec_field']} ?? ''));

            $criteria[] = [
                'key' => $key,
                'label' => $definition['label'],
                'scored' => (bool) ($definition['scored'] ?? false),
                'why_not_scored' => $definition['why_not_scored'] ?? null,
                /* What the specification actually asks for, split the way the
                   legacy forms store it. */
                'required' => $raw === '' ? [] : array_values(array_filter(
                    array_map('trim', explode($delimiter, $raw))
                )),
                'importance' => $importance[$key] ?? JobspecRequirement::PREFERRED,
                'classified' => array_key_exists($key, $importance),
            ];
        }

        return response()->json([
            'jspec_id' => $jspec,
            'position' => $spec->jspec_position ?? null,
            'criteria' => $criteria,
            'note' => 'A requirement nobody has classified counts as preferred, so it can '
                . 'never disqualify an applicant on its own.',
        ]);
    }

    /** Record how one criterion is classified. */
    public function store(Request $request, $jspec)
    {
        $validated = $request->validate([
            'criterion_key' => 'required|string|max:40',
            'importance' => 'required|string|in:mandatory,preferred',
            'note' => 'nullable|string|max:255',
        ]);

        /* Only criteria this system knows about: a key typed into a request
           must not create a classification for something that is never read. */
        if (!array_key_exists($validated['criterion_key'], config('qualification_matching.criteria', []))) {
            return response()->json(['message' => 'That is not a requirement this system matches on.'], 422);
        }

        JobspecRequirement::classify(
            (int) $jspec,
            $validated['criterion_key'],
            $validated['importance'],
            $validated['note'] ?? null,
            Auth::user()->Emp_No ?? null
        );

        return response()->json([
            'success' => true,
            'message' => $validated['importance'] === 'mandatory'
                ? 'Recorded as mandatory. An applicant who does not meet it is flagged, '
                    . 'never rejected automatically.'
                : 'Recorded as preferred.',
        ]);
    }
}

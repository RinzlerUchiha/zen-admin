<?php

/*
|--------------------------------------------------------------------------
| Applications — status table (HireFlow Phase 2.5, Milestone 3)
|--------------------------------------------------------------------------
|
| The one definition of what each application status means. My Applications,
| Home, withdrawal, re-application and the Candidate Pool all read this, so no
| part of the system keeps its own idea of which statuses are "closed".
|
| Keys are the exact values stored in tblapp_applications.status. They are
| stored data: renaming a key orphans existing rows.
|
| Mirror of zen-applicants/config/applications.php, which the applicant portal
| reads. The keys and both flags must match in the two files.
|
|   closed          the application has ended. Closed applications no longer
|                   block a new application to the same posting (the database
|                   enforces one OPEN application per posting).
|   candidate_pool  the candidate did not progress for THIS application and the
|                   record is retained. Candidate Pool is per application, never
|                   a status of the applicant as a whole.
|
| A status that is not listed here is treated as open and not in the pool, so
| an unexpected value can never close an application or pool a candidate.
|
*/

return [

    'statuses' => [
        'Applied' => [
            'closed' => false,
            'candidate_pool' => false,
        ],

        // Every document the application's process asked for was accepted.
        'Documents Complete' => [
            'closed' => false,
            'candidate_pool' => false,
        ],

        // The applicant, or HR on their behalf, ended this one application.
        'Withdrawn' => [
            'closed' => true,
            'candidate_pool' => true,
        ],

        // This application's document deadline passed with requests unresolved.
        'Non-Responsive' => [
            'closed' => true,
            'candidate_pool' => true,
        ],

        // HR decided not to proceed with this application.
        /*
        | Acquired. The hiring decision is made, but the application stays
        | OPEN: documents may still be missing, a position may still require
        | an exam, and onboarding has not happened. Hired is an OUTCOME, not
        | a finish line — closing it here would hide exactly the work HR
        | still has to chase.
        |
        | It closes after the 201 handoff. Never a Candidate Pool member: a
        | hire is not someone to revisit for another role.
        */
        'Hired' => [
            'closed' => false,
            'candidate_pool' => false,
            'label' => 'Hired',
            'hint' => 'Hiring decided. Open until requirements and onboarding are done and the 201 handoff is complete.',
        ],

        /*
        | Did not get past screening or matching, so never reached interview.
        |
        | Distinct from Not Selected on purpose: one applicant was never met,
        | the other was interviewed and not chosen. Without the distinction no
        | report can separate screening loss from interview loss, and the two
        | say completely different things about a posting.
        |
        | No cooldown. Not matching THIS posting's requirements is not a mark
        | against the person -- they may be exactly right for the next one, and
        | the Candidate Pool exists to surface precisely them.
        */
        'Not Shortlisted' => [
            'closed' => true,
            'candidate_pool' => true,
        ],

        'Not Selected' => [
            'closed' => true,
            'candidate_pool' => true,
        ],

        /*
        | The applicant turned the offer down.
        |
        | Their choice, not HR's, which is why this is not Not Selected: that
        | status records a decision HR made. No cooldown either -- declining
        | one offer says nothing about whether they would accept another, and
        | somebody who got as far as an offer is worth keeping in the pool.
        */
        'Offer Declined' => [
            'closed' => true,
            'candidate_pool' => true,
        ],

        /*
        | HR judged the applicant a better fit for a different job post, and a
        | recommendation produced an application there.
        |
        | This application ended, but nothing was refused: the work continues on
        | the application created for the other posting. It is NOT a rejection,
        | so it carries no cooldown — the applicant is free to apply here again.
        | Retained in the Candidate Pool, since someone recommended elsewhere is
        | exactly the kind of candidate worth revisiting.
        */
        'Recommended' => [
            'closed' => true,
            'candidate_pool' => true,
        ],
    ],

    /*
    | Re-application cooldowns, by the status an application closed with.
    |
    | A cooldown applies to the SAME job posting only. Being Not Selected for
    | Job A blocks a new application to Job A for the period; Job B and Job C
    | are unaffected. It is never applicant-wide.
    |
    | Only statuses listed here start a cooldown. Withdrawn and Non-Responsive
    | deliberately do not. The rule is applied in the applicant portal, where
    | applying happens (zen-applicants App\Services\ReapplicationPolicy).
    */
    'cooldowns' => [
        'Not Selected' => ['months' => 6],
    ],

];

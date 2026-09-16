<?php

/*
|--------------------------------------------------------------------------
| Applicant Documents — HR review (HireFlow Phase 2.5, Milestone 2)
|--------------------------------------------------------------------------
|
| Mirror of what HR needs from zen-applicants/config/documents.php. The type
| keys and review-reason keys are stored in zen_applicant and must match the
| applicant portal's config exactly; the labels here are HR's wording.
|
*/

return [

    /* Application-stage documents: the only types HR reviews or requests now. */
    'types' => [
        'resume_cv'    => 'Résumé / CV',
        'picture_2x2'  => '2x2 Picture',
        'cover_letter' => 'Cover Letter',
    ],

    'required' => ['resume_cv', 'picture_2x2'],

    /*
    | Why HR cannot accept a document. Each reason lists the document types it
    | makes sense for, so HR is only offered reasons that apply:
    |
    | - "Expired" is not offered: none of these three documents expires. That
    |   belongs to IDs and clearances at a later stage.
    | - There is no generic "invalid": it tells the applicant nothing to fix.
    |   Its real cases for these documents are covered by the specific reasons.
    | - "Other" always needs a note, because the reason itself says nothing.
    */
    'review_reasons' => [
        'unreadable' => [
            'label' => 'Unclear or unreadable',
            'hint' => 'Blurry, too dark, cut off, low resolution, or the file will not open.',
            'applies_to' => ['resume_cv', 'picture_2x2', 'cover_letter'],
        ],
        'wrong_document' => [
            'label' => 'Wrong document',
            'hint' => 'Not the document requested, e.g. a certificate uploaded as a résumé.',
            'applies_to' => ['resume_cv', 'picture_2x2', 'cover_letter'],
        ],
        'incomplete' => [
            'label' => 'Incomplete',
            'hint' => 'Missing pages or key details, e.g. no contact details or work history.',
            'applies_to' => ['resume_cv', 'cover_letter'],
        ],
        'outdated' => [
            'label' => 'Outdated',
            'hint' => 'Résumé not current, or the photo is not recent.',
            'applies_to' => ['resume_cv', 'picture_2x2'],
        ],
        'photo_requirements' => [
            'label' => 'Does not meet 2x2 photo requirements',
            'hint' => 'Not a formal ID-style photo — e.g. casual shot, group photo, face not clearly visible.',
            'applies_to' => ['picture_2x2'],
        ],
        'details_mismatch' => [
            'label' => 'Details do not match',
            'hint' => 'Name differs from the profile, or the letter is addressed to another company or position.',
            'applies_to' => ['resume_cv', 'cover_letter'],
        ],
        'other' => [
            'label' => 'Other',
            'hint' => 'Explain in the note — the applicant sees it.',
            'applies_to' => ['resume_cv', 'picture_2x2', 'cover_letter'],
            'note_required' => true,
        ],
    ],

    /*
    | Document completion (HireFlow 2.5, Milestone 3).
    |
    | One run at collecting an applicant's outstanding documents. Every request
    | in the run shares one deadline and one attempt counter — there is no
    | per-document deadline and no per-document attempt count.
    |
    | These are the defaults HR is offered. What HR actually chooses is stored
    | on the run itself, so changing a default here never moves a deadline an
    | applicant has already been given.
    */
    'completion' => [

        /* Calendar days: weekends count, Philippine public holidays do not. */
        'deadline_days' => 7,
        'deadline_days_max' => 60,

        /* Rejections allowed across the whole run. */
        'max_attempts' => 3,
        'max_attempts_max' => 20,

        /*
        | Which rows of tngc_hrd2.tbl_holiday stop the clock. '#all' is the
        | nationwide scope; the branch scopes (TAC, ZAM, PGD …) are local
        | holidays, and an applicant is not attached to a branch at this stage.
        | The existing holiday calendar is reused as-is — HR maintains it in
        | Events › Holiday, and nothing here duplicates it.
        */
        'holiday_scope' => '#all',

        'statuses' => [
            'active'               => 'In progress',
            'complete'             => 'Complete',
            'non_responsive'       => 'Non-Responsive',
            'requirements_not_met' => 'Document Requirements Not Met',
            'withdrawn'            => 'Withdrawn',
        ],

        /* The outcomes that retain the candidate rather than progressing them. */
        'pool_statuses' => ['non_responsive', 'requirements_not_met', 'withdrawn'],

        /*
        | What the outcome is written as in tblapp_applications.status, which
        | both systems read. Kept apart from the display labels above, and
        | identical to the same map in zen-applicants/config/documents.php:
        | whichever app closes a run, the application ends up saying the same
        | thing. Values are stored, so changing one orphans existing rows.
        */
        'application_status' => [
            'complete'             => 'Documents Complete',
            'non_responsive'       => 'Non-Responsive',
            'requirements_not_met' => 'Document Requirements Not Met',
            'withdrawn'            => 'Withdrawn',
        ],
    ],

    /* Disk defined in config/filesystems.php, shared with zen-applicants. */
    'disk' => 'applicant_documents',
    'path' => 'applicant/documents',

];

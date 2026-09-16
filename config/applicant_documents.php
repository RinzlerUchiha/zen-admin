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

    /* Disk defined in config/filesystems.php, shared with zen-applicants. */
    'disk' => 'applicant_documents',
    'path' => 'applicant/documents',

];

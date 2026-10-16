<?php

/*
|--------------------------------------------------------------------------
| Candidate matching
|--------------------------------------------------------------------------
|
| What the matcher compares, and how. Everything here is data rather than code
| so it can be read and corrected by whoever owns the policy, without touching
| the service.
|
| THREE RULES THIS FILE EXISTS TO HOLD DOWN
|
|   1. UNKNOWN IS NOT A FAILURE. Every criterion can return 'unknown', and no
|      unknown ever disqualifies anybody. Missing information is a gap in what
|      we were told, not a fact about the applicant.
|
|   2. NOTHING IS WEIGHTED. There is no authoritative weighting for these
|      criteria anywhere -- not in the job specification, not in the process
|      documents, not in the code. Any number would therefore be invented, so
|      there is no score. Ranking is by counts, in a published order.
|
|   3. NOTHING HERE DISQUALIFIES. The matcher advises. HR decides, and an
|      override is recorded with a reason.
|
| WHAT IS DELIBERATELY NOT MATCHED
|
| 'scored' => false below marks the criteria that cannot be compared honestly.
| They are still SHOWN, as evidence for a person to read -- they are simply
| never counted and never produce 'not met'. See each one's note.
|
*/

return [

    /*
    |----------------------------------------------------------------------
    | Education levels
    |----------------------------------------------------------------------
    |
    | The two vocabularies do not line up, and the applicant's is COARSER than
    | the requirement's. Verified from the code on both sides:
    |
    |   requirement side  zen/manpower/includes/manpower_jobspec_config.php
    |                     MP_JOBSPEC_OPTIONS['education'] -- six options
    |   applicant side    zen-applicants EducationController:28
    |                     'in:Primary,Secondary,Tertiary' -- three values
    |
    | So three of the six requirements have NO representable answer. They are
    | mapped to null, which resolves to 'unknown' and never to 'not met': the
    | system cannot tell, and says so, rather than guessing against somebody.
    |
    | Raising this ceiling is a portal change (adding Vocational and a
    | post-graduate level to the applicant form) and is NOT assumed here.
    */
    'education_levels' => [
        /* Requirement option => the rank an applicant must reach, or null
           when the applicant form cannot express it. */
        'High School Graduate' => 2,
        'College Graduate (4 year course)' => 3,

        /* Indistinguishable from a four-year course in applicant data: both
           are 'Tertiary'. The degree title is shown so HR can tell. */
        'Five-year course Graduate' => null,

        /* No applicant value exists for either. */
        'Vocational Course Graduate' => null,
        'Masterate / Doctoral**Specify' => null,

        /* Not a level at all -- handled as a certification. */
        'Licensed' => null,
    ],

    /* The applicant's own values, ranked. From EducationController's
       validation, which is the authoritative list. */
    'applicant_education_levels' => [
        'Primary' => 1,
        'Secondary' => 2,
        'Tertiary' => 3,
    ],

    /*
    | Which of educ_currStatus counts as having FINISHED.
    |
    | Load-bearing, not decorative: a Tertiary row that is still in progress
    | does not satisfy "College Graduate". From EducationController:35,
    | 'in:Completed,Graduated,Currently enrolled'.
    */
    'education_completed_statuses' => ['Completed', 'Graduated'],

    /*
    |----------------------------------------------------------------------
    | Work experience
    |----------------------------------------------------------------------
    |
    | The cleanest criterion, and the only one that is fully computable: the
    | requirement bands are ordered, and the applicant's months are summed from
    | tblapp_employment.empl_from / empl_to.
    |
    | Overlapping periods are MERGED rather than added, or two concurrent jobs
    | would read as twice the experience.
    |
    | Verified from MP_JOBSPEC_OPTIONS['workexp'].
    */
    'experience_bands' => [
        'Not Necessary (none)' => 0,
        '6 months to 1 year' => 6,
        '1 to 2 years' => 12,
        '2 years or more' => 24,
        '5 years or more' => 60,
    ],

    /*
    |----------------------------------------------------------------------
    | The criteria
    |----------------------------------------------------------------------
    |
    | 'scored' => false means shown as evidence but never counted and never
    | 'not met'. Each one says why, because "we did not compare this" is a
    | claim that has to be justified on screen.
    */
    'criteria' => [

        'education' => [
            'label' => 'Education',
            'jobspec_field' => 'jspec_education',
            'scored' => true,
        ],

        'workexp' => [
            'label' => 'Work experience',
            'jobspec_field' => 'jspec_workexp',
            'scored' => true,
        ],

        'certification' => [
            'label' => 'Certifications and licences',
            'jobspec_field' => 'jspec_education',
            'scored' => false,
            /* tblapp_certificate mixes licences with seminar attendance
               (cert_title, cert_speaker, cert_date), so the presence of a
               specific licence cannot be established from it. */
            'why_not_scored' => 'Licences and seminar certificates are stored together, '
                . 'so a specific licence cannot be confirmed automatically.',
        ],

        'computerskill' => [
            'label' => 'Computer skills',
            'jobspec_field' => 'jspec_computerskill',
            'scored' => false,
            /* The requirement is three fixed options; the applicant side is
               tblapp_skill_software.appss_content, free text up to 1000
               characters. Matching prose against a fixed list is guesswork. */
            'why_not_scored' => 'The applicant records these as free text, so they '
                . 'cannot be matched against the three fixed options.',
        ],

        'competencies' => [
            'label' => 'Competencies',
            'jobspec_field' => 'jspec_competencies',
            'scored' => false,
            /* The requirement is free prose; the applicant side is seven
               coarse categories (tbl_skill_category) and fifteen types.
               Matching "Technical Skills" against a paragraph is exactly the
               opaque scoring this design forbids. */
            'why_not_scored' => 'Written as prose here and chosen from broad categories '
                . 'by the applicant, so the two cannot be compared mechanically.',
        ],

        'techcompetencies' => [
            'label' => 'Technical competencies',
            'jobspec_field' => 'jspec_techcompetencies',
            'scored' => false,
            'why_not_scored' => 'Written as prose, with nothing structured to compare it to.',
        ],

        'otherskill' => [
            'label' => 'Other skills',
            'jobspec_field' => 'jspec_otherskill',
            'scored' => false,
            'why_not_scored' => 'Written as prose, with nothing structured to compare it to.',
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Never scored, and never used to disqualify
    |----------------------------------------------------------------------
    |
    | These exist on the job specification and are shown as context for the
    | posting, but they must not enter a ranking:
    |
    |   agerange, sex   Ranking people on either would build discrimination
    |                   into the shortlist. Not a technical limitation.
    |   emplstat        Describes the OFFER (probationary, contractual), not
    |                   the candidate, so there is nothing to match it against.
    */
    'never_scored' => ['jspec_agerange', 'jspec_sex', 'jspec_emplstat'],

    /*
    |----------------------------------------------------------------------
    | Ranking
    |----------------------------------------------------------------------
    |
    | Lexicographic, in this order, and printed on the screen beside the list.
    | No weights, because no authoritative weighting exists; a single blended
    | percentage would be a number the system made up.
    |
    | The last key is a deterministic tie-break, so the same inputs always
    | produce the same order.
    */
    'ranking' => [
        'mandatory_unmet_asc',
        'mandatory_met_desc',
        'preferred_met_desc',
        'unknown_asc',
        'applied_at_asc',
    ],

    /* The delimiters the legacy forms store multi-value fields with.
       MP_JOBSPEC_DELIM, and the education detail separator. */
    'delimiters' => [
        'item' => '%#',
        'detail' => '%&',
    ],
];

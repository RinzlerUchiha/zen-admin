<?php

/*
|--------------------------------------------------------------------------
| Applicant assessments — access codes and attempt status (HireFlow 2.5)
|--------------------------------------------------------------------------
| The assessments themselves run in zen-applicants (its
| config/application_form.php "assessments" block is the source of truth for
| durations and rules). HR's side is small: issue the one-time access code that
| opens them, and see where each attempt stands. Grading is unchanged — it stays
| with config/exams.php and the existing assessment tabs.
|
| code_length, code_minutes and max_failures mirror zen-applicants' "access"
| settings; grace_seconds mirrors its "attempts.grace_seconds".
*/
return [
    'code_length' => 6,
    'code_minutes' => 30,
    'max_failures' => 5,

    // A running attempt whose page has been quiet longer than this is
    // interrupted. zen-applicants records that the next time the applicant
    // opens it; HR's view applies the same rule so it is not shown as running.
    'grace_seconds' => 180,

    /*
    | The eleven assessments, in the order HR sees them. Keyed as stored in
    | tblapp_assessment_attempts.
    |
    |   tab     the applicant-profile tab that shows the result
    |   table   the existing result table (a row = a result)
    |   date    that table's date column
    |   kind    questionnaire | aptitude (graded against config/exams.php)
    |   items   for aptitude tests: how many questions the score is out of
    */
    'list' => [
        'enneagram' => ['label' => 'Enneagram', 'tab' => 'enneagram', 'table' => 'tblapp_enneagramtest', 'date' => 'enneagram_dt', 'kind' => 'questionnaire'],
        'tapt' => ['label' => 'TAPT', 'tab' => 'tapt', 'table' => 'tblapp_tapt', 'date' => 'tapt_dt', 'kind' => 'questionnaire'],
        'disc' => ['label' => 'DISC', 'tab' => 'disc', 'table' => 'tblapp_disc', 'date' => 'disc_dt', 'kind' => 'questionnaire'],
        'miq' => ['label' => 'Multiple Intelligence', 'tab' => 'miq', 'table' => 'tblapp_miq', 'date' => 'miq_dt', 'kind' => 'questionnaire'],
        'color' => ['label' => 'What color are you?', 'tab' => 'color', 'table' => 'tblapp_whatcolorareyou', 'date' => 'wcay_dt', 'kind' => 'questionnaire'],
        'vak' => ['label' => 'VAK', 'tab' => 'vak', 'table' => 'tblapp_vak', 'date' => 'vak_dt', 'kind' => 'questionnaire'],
        'why_i_work' => ['label' => 'Why I Work', 'tab' => 'why-i-work', 'table' => 'tblapp_whyiwork', 'date' => 'wiw_dt', 'kind' => 'questionnaire'],
        'career_anchors' => ['label' => 'Career Anchors', 'tab' => 'career-anchors', 'table' => 'tblapp_careeranchors', 'date' => 'career_dt', 'kind' => 'questionnaire'],
        'abstract_reasoning' => ['label' => 'Basic Abstract Reasoning', 'tab' => 'abstract-reasoning', 'table' => 'tblapp_basicabstract', 'date' => 'abstract_dt', 'kind' => 'aptitude', 'items' => 10],
        'basic_math' => ['label' => 'Basic Math', 'tab' => 'basic-math', 'table' => 'tblapp_basicmath', 'date' => 'math_dt', 'kind' => 'aptitude', 'items' => 12],
        'maya' => ['label' => 'Maya', 'tab' => 'maya', 'table' => 'tblapp_maya', 'date' => 'maya_dt', 'kind' => 'aptitude', 'items' => 60],
    ],

    // Status => [short label, HireFlow status chip], for the result tabs and
    // the assessment menu. "not_started" means no attempt and no result.
    'badges' => [
        'not_started' => ['Not taken', 'hf-chip-lock'],
        'active' => ['In progress', 'hf-chip-accent'],
        'interrupted' => ['Paused', 'hf-chip-caution'],
        'submitted' => ['Completed', 'hf-chip-ok'],
        'timed_out' => ['Time ran out', 'hf-chip-lock'],
    ],

    // Attempt status => how HR reads it.
    'statuses' => [
        'active' => 'In progress',
        'interrupted' => 'Interrupted — needs a new code to resume',
        'submitted' => 'Submitted',
        'timed_out' => 'Time ran out',
    ],
];

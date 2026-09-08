<?php

/**
 * Public Job Ad — controlled copy and field policy.
 *
 * Everything in this file is either (a) company copy we approve and publish
 * verbatim, or (b) rules describing how HireFlow's Job Spec fields are read.
 * Nothing here may state a job fact: hooks, headings and connectives are
 * questions and labels only. Job facts come exclusively from the Job Spec.
 *
 * Field behaviour below mirrors HireFlow's actual form
 * (zen/manpower/public/manpower_jobspec_form.php + jobspec_save.php):
 *   - checkbox groups are stored joined with "%#"
 *   - an option and its free-text detail are joined with "%&"
 *   - textareas are stored raw, so they split on lines (and optionally commas)
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Field policy
    |--------------------------------------------------------------------------
    | split: 'delimiter'    → explode on "%#"           (checkbox groups)
    |        'lines'        → newlines / ; / •          (prose textareas)
    |        'lines_commas' → the above, then commas outside brackets
    | public: may this field ever appear in a public ad?
    */
    'fields' => [
        'jspec_duties'           => ['split' => 'lines',        'public' => true,  'max' => 8],
        'jspec_techcompetencies' => ['split' => 'lines_commas', 'public' => true,  'max' => 8],
        'jspec_competencies'     => ['split' => 'lines_commas', 'public' => true,  'max' => 8],
        'jspec_computerskill'    => ['split' => 'delimiter',    'public' => true,  'max' => 6],
        'jspec_otherskill'       => ['split' => 'lines_commas', 'public' => true,  'max' => 6],
        'jspec_education'        => ['split' => 'delimiter',    'public' => true,  'max' => 6],
        'jspec_workexp'          => ['split' => 'delimiter',    'public' => true,  'max' => 5],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage delimiters
    |--------------------------------------------------------------------------
    | "%#" separates multi-select entries.
    | "%&" separates a selected option from its free-text detail, e.g.
    |      "College Graduate (4 year course)%&Any IT Technical Course".
    |
    | Both halves of a "%&" pair are legitimate information: the option and the
    | detail are kept and joined with detail_separator for display. Neither
    | delimiter may ever appear in a published ad — AdSafetyValidator asserts
    | this, so an unhandled delimiter fails loudly instead of leaking.
    */
    'entry_delimiter'  => '%#',
    'detail_delimiter' => '%&',
    'detail_separator' => ' — ',

    /*
    | Never published. The validator asserts none of these values appear in the
    | rendered ad, so a future edit cannot leak them by accident.
    */
    'excluded_fields' => [
        'jspec_sex', 'jspec_agerange', 'jspec_headsnum', 'jspec_created_by',
        'jspec_mpa', 'jspec_mpb', 'jspec_mpc', 'jspec_mpd', 'jspec_mpe',
        'jspec_mpf', 'jspec_mpg', 'jspec_tapt', 'jspec_enneagram',
        'jspec_learnstyle', 'jspec_career', 'jspec_motivation',
        'jspec_personality', 'jspec_ravenl', 'jspec_ravena', 'jspec_ravenh',
        'jspec_leadership', 'jspec_reason', 'jspec_remarks',
    ],

    /*
    |--------------------------------------------------------------------------
    | Presentation fixes
    |--------------------------------------------------------------------------
    | Spelling and spacing only — never meaning. Applied to source text before
    | rendering AND to the origin string before validation, so a fix can never
    | smuggle in a new word. Keys are matched case-insensitively as whole words.
    */
    'typo_map' => [
        'Acces'       => 'Access',
        'Power Point' => 'PowerPoint',
        'Peach Tree'  => 'PeachTree',
        'Quick Books' => 'QuickBooks',
        'etc. )'      => 'etc.)',
    ],

    /*
    | Words the renderer may add inside a source line purely to make a
    | parenthetical read as prose. Nothing here carries meaning about a job.
    */
    'filler_tokens' => ['including'],

    /* Rewrite "X (a, b, c)" as "X, including a, b, c". */
    'parenthetical_to_prose' => true,

    /* Publish the resolved department name (tbl_department.Dept_Name). */
    'show_department' => true,

    /* Section names are internal-sounding; off until someone asks for them. */
    'show_section' => false,

    /*
    |--------------------------------------------------------------------------
    | Evidence tags — drive hook selection
    |--------------------------------------------------------------------------
    | Scored ONLY against Job Spec content fields (duties, technical
    | competencies, competencies, computer skills, other skills, work
    | experience). The job title is deliberately not an input: a title may
    | not claim work the spec does not describe.
    |
    | Order below is priority order. min_hits guards vague keywords.
    */
    'tags' => [
        'supervisory' => [
            'min_hits' => 1,
            // Verbs the ROLE performs. Deliberately excludes bare "supervis",
            // which also matches "reports to the Supervisor" — a clerk who
            // reports to one does not supervise anybody.
            'keywords' => ['supervises', 'supervising', 'oversees', 'manages the',
                           'team lead', 'subordinates', 'direct reports', 'heads the'],
            'hook'     => '🚀 READY KA NA BANG MAG-LEAD? ✨',
        ],
        'technical_repair' => [
            'min_hits' => 1,
            'keywords' => ['troubleshoot', 'repair', 'preventive maintenance', 'hardware',
                           'network', 'cabling', 'install', 'cctv', 'equipment maintenance'],
            'hook'     => '🔧 MAHILIG KA BA MAG-AYOS AT MAG-TROUBLESHOOT? ✨',
        ],
        'programming' => [
            'min_hits' => 1,
            'keywords' => ['programming', 'developer', 'software development', 'coding',
                           'php', 'mysql', 'sql', 'java', 'laravel', 'web development'],
            'hook'     => '💻 MAHILIG KA BA SA CODING AT SYSTEMS? ✨',
        ],
        'accounting_finance' => [
            'min_hits' => 1,
            'keywords' => ['accounting', 'financial statement', 'bookkeeping', 'payroll',
                           'audit', 'peach tree', 'peachtree', 'quick books', 'quickbooks',
                           'ledger', 'billing'],
            'hook'     => '📒 MAY BACKGROUND KA BA SA ACCOUNTING? ✨',
        ],
        'inventory_logistics' => [
            'min_hits' => 1,
            'keywords' => ['inventory', 'stock', 'warehouse', 'receiving', 'delivery',
                           'dispatch', 'stockroom', 'counting'],
            'hook'     => '📦 MAY MATA KA BA SA DETALYE AT STOCKS? ✨',
        ],
        'sales_customer' => [
            'min_hits' => 1,
            'keywords' => ['sales', 'customer service', 'client', 'selling', 'cashier',
                           'promodiser', 'promodizer', 'merchandising'],
            'hook'     => '✨ WALANG WORK? BAKA ITO NA ANG SIGN MO! ✨',
        ],
        'data_records' => [
            'min_hits' => 2,
            'keywords' => ['data', 'records', 'encoding', 'encode', 'filing', 'documentation',
                           'reports', 'database', 'monitoring'],
            'hook'     => '📊 MAHILIG KA BA SA DETALYE AT MAAYOS NA RECORDS? ✨',
        ],
        'design_layout' => [
            'min_hits' => 1,
            'keywords' => ['layout', 'designing', 'publisher', 'corel', 'pagemaker',
                           'photoshop', 'graphics'],
            'hook'     => '🎨 MAY SKILLS KA BA SA LAYOUT AT DESIGN? ✨',
        ],
        'office_software' => [
            'min_hits' => 2,
            'keywords' => ['ms office', 'word', 'excel', 'power point', 'powerpoint',
                           'visio', 'access', 'spreadsheet'],
            'hook'     => '💻 MAY ALAM KA BA SA OFFICE SOFTWARE? ✨',
        ],
    ],

    /* Used when no tag reaches its threshold. */
    'neutral_hook' => '📣 MAY BAKANTE KAMI — BAKA IKAW NA! ✨',

    /*
    |--------------------------------------------------------------------------
    | Connective copy
    |--------------------------------------------------------------------------
    | Fixed strings only. The renderer selects a key; it never interpolates
    | into one. That is what makes it structurally impossible for connective
    | text to contain a job fact.
    */
    'connectives' => [
        'duties_heading'        => 'Ano ang gagawin mo:',
        'tech_heading'          => 'Technical skills na kailangan:',
        'competencies_heading'  => 'Competencies na hinahanap namin:',
        'computer_heading'      => 'Computer skills na gagamitin mo:',
        'other_heading'         => 'Plus points kung meron ka nito:',
        'education_heading'     => 'Educational attainment na hinahanap namin:',
        'experience_heading'    => 'Karanasan na kailangan:',
        'no_experience'         => 'Walang experience? Okay lang — may training kami! 💪',
        'skills_lead'           => 'Ito ang mga skills na gagamitin mo sa trabahong ito:',
        'rich_lead'             => 'Heto ang mga aasikasuhin mo araw-araw:',
        'sparse_lead'           => 'Heto ang mga hinahanap namin:',
    ],

    /* Closing instruction — company copy. */
    'apply_line' => '📩 Mag-apply online — i-click ang APPLY NOW sa ibaba.',
];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Where a job posting gets advertised
    |--------------------------------------------------------------------------
    |
    | HireFlow does not publish to LinkedIn, JobStreet or PhilJobNet itself —
    | that needs each platform's API, which we do not have. What it does is
    | let HR record where they posted, hand them a tagged Careers link per
    | place, and count what comes back through that link.
    |
    | So each channel here is a place HR posts BY HAND. The counting is
    | HireFlow's side of the trip only:
    |
    |   visits        someone opened the Careers page through this channel's
    |                 tagged link
    |   applications  someone who arrived that way went on to apply
    |
    | What we CANNOT report is the platform's own numbers — LinkedIn
    | impressions, JobStreet views. Nothing here should ever be labelled as
    | those. A channel's "visits" means visits to OUR page, arriving from
    | there. See tbl_job_posting_channel and PostingChannels.
    |
    | This is config rather than a table for the same reason job_ad_templates
    | is: the list changes when someone decides to advertise somewhere new,
    | which is a deploy, not a Tuesday. HR can still record a place that is
    | not listed here — an unknown key is kept and shown by its key, so no
    | one is blocked waiting for a config change.
    |
    | Adding a channel: give it a key that will never change (it goes in
    | public URLs and is stored on every application that came through it),
    | then a name HR will recognise.
    |
    | `external` marks the places HR posts to by hand, which are the ones
    | that get a tagged link and a Posted / Not Posted state. A channel with
    | external => false is somewhere traffic arrives on its own; it is
    | counted but never "posted to".
    |
    | `api` is deliberately false everywhere today. It is the flag a future
    | integration would flip, so the place that decides "manual or automatic"
    | already exists and callers do not have to be rewritten around it.
    |
    */

    'channels' => [

        // Not a place HR posts. This is the bucket for everyone who reached
        // the Careers page without a tagged link — typed the URL, a bookmark,
        // a search result, a forwarded message. It is always present on a
        // posting and cannot be switched off, because the traffic exists
        // whether or not anyone is tracking it.
        'direct' => [
            'name' => 'Zen Careers (direct)',
            'external' => false,
            'api' => false,
            'description' => 'Reached the Careers page without a tagged link.',
        ],

        'linkedin' => [
            'name' => 'LinkedIn',
            'external' => true,
            'api' => false,
            'description' => 'Posted by hand on LinkedIn.',
        ],

        'jobstreet' => [
            'name' => 'JobStreet',
            'external' => true,
            'api' => false,
            'description' => 'Posted by hand on JobStreet.',
        ],

        'philjobnet' => [
            'name' => 'PhilJobNet',
            'external' => true,
            'api' => false,
            'description' => 'Posted by hand on PhilJobNet.',
        ],

        'facebook' => [
            'name' => 'Facebook',
            'external' => true,
            'api' => false,
            'description' => 'Posted by hand on a Facebook page or group.',
        ],

        'indeed' => [
            'name' => 'Indeed',
            'external' => true,
            'api' => false,
            'description' => 'Posted by hand on Indeed.',
        ],

        // Not a link anyone clicks. This is how HR records that someone came
        // in through a job fair, a referral or off the street, so that
        // "where do our applicants come from" answers include them. Walk-in
        // intake is not built yet; the channel is here so that when it is,
        // it writes to the same field as everything else.
        'walk_in' => [
            'name' => 'Walk-in / Referral / Job fair',
            'external' => false,
            'api' => false,
            'description' => 'Recorded by HR at intake rather than arriving through a link.',
        ],
    ],

    /*
    | The channel an application is credited to when it arrived with no tag.
    | Must be a key above.
    */
    'default' => 'direct',

    /*
    | The query parameter carrying the channel on a Careers link:
    |
    |     https://…/careers/15?src=linkedin
    |
    | Short, survives being pasted into a post body, and readable to whoever
    | is looking at it. Changing it orphans links already posted on external
    | sites, which nobody can go back and edit — so treat it as permanent.
    */
    'parameter' => 'src',

    /*
    | Where the public Careers portal lives (zen-applicants), with no
    | trailing slash. The tagged links HR copies are built from this, and
    | they get pasted onto LinkedIn and JobStreet where nobody can edit them
    | afterwards — so this has to be the address the public actually reaches,
    | not localhost.
    |
    | Set CAREERS_URL in .env. Until it is set, HR sees a plain warning in
    | place of the links rather than a link that silently goes nowhere.
    */
    'careers_url' => rtrim((string) env('CAREERS_URL', ''), '/'),
];

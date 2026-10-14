<?php

namespace App\Http\Controllers\Recruitment;

use App\Http\Controllers\Controller;
use App\Models\Applicant\ApplicantApplication;

/**
 * The Candidate Pool, as a page.
 *
 * People who did not go forward on one application and whose record is kept:
 * withdrawn, non-responsive, not selected. The pool is per APPLICATION, never
 * a property of the person — the same applicant can be pooled against one
 * posting and live on another.
 *
 * This controller renders a page and nothing else. The list, the eligibility
 * rules, the cooldown, the blacklist flag and the recommendation all belong
 * to code that already existed:
 *
 *   - which statuses pool          config/applications.php, via
 *                                  ApplicantApplication::candidatePoolStatuses()
 *   - when they may reapply        ApplicantApplication::cooldownEndsFor()
 *   - the rows themselves          ApplicantIntakeController::candidatePool(),
 *                                  reached at this page's /data address
 *   - putting one forward          ApplicationRecommendationService, through
 *                                  the recommendation endpoints
 *
 * Nothing here re-decides any of it. A second definition of "eligible" is
 * exactly the duplicate business rule this page must not become.
 */
class CandidatePoolController extends Controller
{
    public function index()
    {
        return view('pages.recruitment', [
            'main_link' => 'recruitment',
            'sub_link' => 'candidate-pool',
            'maincat' => 'candidate-pool',
            'page' => 'pages.recruitment.candidate-pool.index-content',

            /*
            | The pooling statuses, named by config rather than by this page.
            |
            | The filter chips are built from whatever config says pools, so
            | adding a status there puts it on this page with no change here
            | and no chance of the two disagreeing about what is in the pool.
            */
            'poolStatuses' => ApplicantApplication::candidatePoolStatuses(),
        ]);
    }
}

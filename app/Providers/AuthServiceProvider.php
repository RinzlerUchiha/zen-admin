<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        /*
        | Applicant documents (HireFlow 2.5 · M2). No new permission: these are
        | names for the existing HRIS module "eappprofile" (Employee
        | Application Profile) in tngc_hrd2.tbl_sysassign, checked through the
        | same User::userAccess() the rest of zen-admin uses.
        |
        |   view                 see an applicant's documents and open the files
        |   directedit or hire   accept a document, mark it for replacement,
        |                        request one
        |
        | "hire" is included because it is the module's recruitment decision
        | right: someone allowed to hire an applicant must be able to check that
        | applicant's résumé. "view" alone stays read-only.
        */
        Gate::define('applicant-documents.view', fn (User $user) => $user->userAccess('eappprofile', 'view'));

        Gate::define('applicant-documents.review', fn (User $user) => $user->userAccess('eappprofile', 'view')
            && ($user->userAccess('eappprofile', 'directedit') || $user->userAccess('eappprofile', 'hire')));

        /*
        | Deciding on one application (HireFlow 2.5 · M3): withdrawing it on the
        | applicant's behalf, or marking it Not Selected. Again no new
        | permission — the same eappprofile rights as reviewing documents, since
        | both are HR's recruitment decisions about this applicant.
        */
        Gate::define('applicant-applications.decide', fn (User $user) => $user->userAccess('eappprofile', 'view')
            && ($user->userAccess('eappprofile', 'directedit') || $user->userAccess('eappprofile', 'hire')));

        /*
        | Issuing an applicant's assessment access code (HireFlow 2.5): the same
        | rights as HR's other actions on an applicant.
        */
        Gate::define('applicant-assessments.issue-access', fn (User $user) => $user->userAccess('eappprofile', 'view')
            && ($user->userAccess('eappprofile', 'directedit') || $user->userAccess('eappprofile', 'hire')));

        /*
        | Seeing every manpower request in zen-admin's Manpower page. The
        | legacy HRIS right (personnelreq "viewall") still grants it; so do
        | HireFlow's own HR rights, since HR runs the hiring from here and
        | Phase 1 gives Admin/HR all requests. Without either, the page keeps
        | showing only what the employee's approver assignment covers.
        */
        Gate::define('manpower-requests.view-all', fn (User $user) => $user->userAccess('personnelreq', 'viewall')
            || ($user->userAccess('eappprofile', 'view')
                && ($user->userAccess('eappprofile', 'directedit') || $user->userAccess('eappprofile', 'hire'))));

        /*
        | Hiring an applicant (creating their employee record): the HRIS right
        | that exists for exactly this, eappprofile "hire".
        */
        Gate::define('applicant.hire', fn (User $user) => $user->userAccess('eappprofile', 'view')
            && $user->userAccess('eappprofile', 'hire'));

        /*
        | Recording interview details: the same rights as HR's other actions on
        | an applicant (above). Anyone who can view the profile can still read
        | them.
        */
        Gate::define('applicant.interview-details.save', fn (User $user) => $user->userAccess('eappprofile', 'view')
            && ($user->userAccess('eappprofile', 'directedit') || $user->userAccess('eappprofile', 'hire')));
    }
}

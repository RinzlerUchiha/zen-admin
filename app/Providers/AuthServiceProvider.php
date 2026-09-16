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
    }
}

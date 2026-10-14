<?php

namespace App\Providers;

use App\Models\Employee;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use stdClass;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
        | The Job Postings step in the recruitment sidebar carries the number of
        | approved positions that still have no posting.
        |
        | It is the figure that step exists to act on, and without it the item
        | was indistinguishable from the ones either side of it. Shared with the
        | shell rather than passed from each controller, because the sidebar is
        | drawn on every recruitment page and only one of them was computing it.
        |
        | Grouped by job, so the badge counts the same way the page does: two
        | requests for one position are one thing to post, not two.
        */
        \Illuminate\Support\Facades\View::composer('pages.recruitment', function ($view) {
            $view->with('rcWaitingToPost', \Illuminate\Support\Facades\Cache::remember(
                'hireflow.waiting-to-post',
                now()->addSeconds(30),
                function () {
                    $posted = \App\Models\Recruitment\JobPosting::whereIn('status', ['Draft', 'Published'])
                        ->pluck('request_position_id');

                    $positions = \App\Models\Recruitment\HireflowManpowerRequest::approved()
                        ->with('positions')->get()
                        ->pluck('positions')->flatten()
                        ->reject(fn ($p) => $posted->contains($p->id));

                    return \App\Services\Recruitment\PositionGrouping::byJob($positions)->count();
                }
            ));
        });

        if (app()->environment('production')) {
            URL::forceRootUrl(config('app.url'));
            URL::forceScheme('https');
        }
    }
}

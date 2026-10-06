<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        /*
        | The Zen-Hub interviewer bridge.
        |
        | Its own limiter because the default one counts per IP, and every
        | request from Zen-Hub arrives from the same server: a handful of
        | interviewers working at once would exhaust a shared budget and lock
        | each other out.
        |
        | Counted per employee instead, taken from the header Zen-Hub sends.
        | That header is not yet verified at this point — rate limiting runs
        | before the signature check — but a forged one buys nothing, since the
        | request it accompanies still fails verification. Requests with no
        | header at all fall back to the IP, so an unsigned flood is still
        | limited.
        */
        RateLimiter::for('hireflow', function (Request $request) {
            $empno = (string) $request->header('X-HF-Employee', '');

            return Limit::perMinute(120)->by($empno !== '' ? 'hf:' . $empno : 'hf-ip:' . $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}

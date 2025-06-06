<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
        Inertia::share('unreadNotifications', function () {
            if (Auth::check()) {
                return Auth::user()
                    ->notifications()
                    ->whereNull('read_at')
                    ->count();
            }
            return 0;
        });
    }
}

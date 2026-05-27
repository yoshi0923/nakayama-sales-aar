<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Socialite Azure プロバイダー登録
        Event::listen(SocialiteWasCalled::class, \SocialiteProviders\Azure\AzureExtendSocialite::class);

        // ゲート定義
        Gate::define('admin', fn($user) => $user->role === 'admin');
        Gate::define('view-analysis', fn($user) => in_array($user->role, ['admin', 'manager', 'viewer']));
        Gate::define('manage-users', fn($user) => $user->role === 'admin');
    }
}

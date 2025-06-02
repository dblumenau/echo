<?php

namespace App\Providers;

use App\Models\User;
use App\Models\WordPair;
use App\Policies\UserPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Register policies
        Gate::policy(User::class, UserPolicy::class);
        
        // Define gates for vocabulary management
        Gate::define('manage-word-pairs', function (User $user) {
            return $user->canManageWordPairs();
        });
        
        Gate::define('manage-categories', function (User $user) {
            return $user->isSuperAdmin();
        });
        
        Gate::define('edit-word-pair', function (User $user, WordPair $wordPair) {
            return $user->isSuperAdmin() || $wordPair->user_id === $user->id;
        });
        
        Gate::define('delete-word-pair', function (User $user, WordPair $wordPair) {
            return $user->isSuperAdmin() || $wordPair->user_id === $user->id;
        });
    }
}

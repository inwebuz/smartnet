<?php

namespace App\Providers;

use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Policies\StudioPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::before(function (User $user, string $ability, array $arguments): ?bool {
            if (isset($arguments[0]) && is_string($arguments[0]) && in_array($ability, ['viewAny', 'create', 'assign'], true)) {
                return $user->hasStudioPermission(StudioPolicy::resourceFor($arguments[0]), match ($ability) {
                    'viewAny' => 'view',
                    default => $ability,
                });
            }

            return null;
        });
        foreach ([Lead::class, Client::class, Project::class, ProjectTask::class, Service::class, CaseStudy::class, User::class, Role::class, SiteSetting::class] as $model) {
            Gate::policy($model, StudioPolicy::class);
        }
    }
}

<?php

namespace App\Policies;

use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StudioPolicy
{
    public static function resourceFor(Model|string $subject): string
    {
        return match (is_string($subject) ? $subject : $subject::class) {
            Lead::class => 'leads',
            Client::class => 'clients',
            Project::class => 'projects',
            ProjectTask::class => 'tasks',
            Service::class => 'services',
            ServiceCategory::class => 'categories',
            Technology::class => 'technologies',
            CaseStudy::class => 'cases',
            User::class => 'team',
            Role::class => 'roles',
            SiteSetting::class => 'settings',
        };
    }

    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Model $model): bool
    {
        return $user->hasStudioPermission(self::resourceFor($model), 'view');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Model $model): bool
    {
        return $user->hasStudioPermission(self::resourceFor($model), 'update');
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->hasStudioPermission(self::resourceFor($model), 'delete');
    }

    public function publish(User $user, Model $model): bool
    {
        return $user->hasStudioPermission(self::resourceFor($model), 'publish');
    }

    public function assign(User $user, Model $model): bool
    {
        return $user->hasStudioPermission(self::resourceFor($model), 'assign');
    }
}

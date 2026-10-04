<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $taskQuery = ProjectTask::where('assignee_id', auth()->id())->where('status', '!=', 'done');

        return view('studio.dashboard', [
            'newLeads' => Gate::allows('viewAny', Lead::class) ? Lead::where('status', 'new')->count() : null,
            'activeProjects' => Gate::allows('viewAny', Project::class) ? Project::whereIn('status', ['planned', 'active'])->count() : null,
            'myTasksCount' => Gate::allows('viewAny', ProjectTask::class) ? (clone $taskQuery)->count() : null,
            'myTasks' => Gate::allows('viewAny', ProjectTask::class) ? $taskQuery->with('project')->latest()->limit(8)->get() : collect(),
        ]);
    }
}

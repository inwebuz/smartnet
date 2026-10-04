<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{
    private function model(string $resource): string
    {
        return match ($resource) {
            'clients' => Client::class,
            'projects' => Project::class,
            'tasks' => ProjectTask::class,
            'services' => Service::class,
            'cases' => CaseStudy::class,
            default => abort(404),
        };
    }

    public function index(string $resource)
    {
        $model = $this->model($resource);
        Gate::authorize('viewAny', $model);

        return view('studio.resources.index', ['resource' => $resource, 'model' => $model, 'items' => $model::latest()->paginate(20)]);
    }

    public function create(string $resource)
    {
        $model = $this->model($resource);
        Gate::authorize('create', $model);

        return $this->form($resource, new $model);
    }

    public function edit(string $resource, int $id)
    {
        $item = $this->model($resource)::findOrFail($id);
        Gate::authorize('update', $item);

        return $this->form($resource, $item);
    }

    private function form(string $resource, object $item)
    {
        return view('studio.resources.form', [
            'resource' => $resource,
            'item' => $item,
            'clients' => Client::orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(),
            'staff' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $model = $this->model($resource);
        Gate::authorize('create', $model);
        $item = $model::create($this->validated($request, $resource));

        return redirect()->route('studio.resources.edit', [$resource, $item->id])->with('message', 'Запись создана.');
    }

    public function update(Request $request, string $resource, int $id)
    {
        $item = $this->model($resource)::findOrFail($id);
        Gate::authorize('update', $item);
        $data = $this->validated($request, $resource, $id);
        if (array_key_exists('assignee_id', $data) && $data['assignee_id'] != $item->assignee_id) {
            Gate::authorize('assign', $item);
        }
        $item->update($data);

        return back()->with('message', 'Изменения сохранены.');
    }

    private function validated(Request $request, string $resource, ?int $id = null): array
    {
        $data = match ($resource) {
            'clients' => $request->validate([
                'name' => ['required', 'string', 'max:255'], 'company' => ['nullable', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'],
                'notes' => ['nullable', 'string'],
            ]),
            'projects' => $request->validate([
                'client_id' => ['required', 'exists:clients,id'], 'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'], 'status' => ['required', Rule::in(['planned', 'active', 'paused', 'done'])],
                'assignee_id' => ['nullable', 'exists:users,id'], 'starts_on' => ['nullable', 'date'], 'due_on' => ['nullable', 'date'],
            ]),
            'tasks' => $request->validate([
                'project_id' => ['required', 'exists:projects,id'], 'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'], 'status' => ['required', Rule::in(['todo', 'doing', 'done'])],
                'priority' => ['required', Rule::in(['low', 'normal', 'high'])], 'assignee_id' => ['nullable', 'exists:users,id'],
                'due_on' => ['nullable', 'date'],
            ]),
            'services', 'cases' => $request->validate([
                'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique($resource === 'cases' ? 'case_studies' : 'services', 'slug')->ignore($id)],
                'title_ru' => ['required', 'string', 'max:255'], 'title_uz' => ['required', 'string', 'max:255'],
                'summary_ru' => ['required', 'string', 'max:1000'], 'summary_uz' => ['required', 'string', 'max:1000'],
                'description_ru' => ['nullable', 'string'], 'description_uz' => ['nullable', 'string'],
                'sort_order' => ['required', 'integer', 'min:0'],
                'image_path' => ['nullable', 'string', 'max:255'],
            ]),
        };
        if (in_array($resource, ['services', 'cases'], true)) {
            $data['title'] = ['ru' => $data['title_ru'], 'uz' => $data['title_uz']];
            $data['summary'] = ['ru' => $data['summary_ru'], 'uz' => $data['summary_uz']];
            unset($data['title_ru'], $data['title_uz'], $data['summary_ru'], $data['summary_uz']);
            if ($resource === 'services') {
                unset($data['image_path']);
            }
        }
        if (array_key_exists('assignee_id', $data) && $data['assignee_id'] !== null) {
            Gate::authorize('assign', $this->model($resource));
        }
        if ($resource === 'tasks') {
            $data['completed_at'] = $data['status'] === 'done' ? now() : null;
        }

        return $data;
    }

    public function destroy(string $resource, int $id)
    {
        $item = $this->model($resource)::findOrFail($id);
        Gate::authorize('delete', $item);
        if ($item instanceof Client && $item->projects()->exists()) {
            return back()->withErrors(['client' => 'Сначала удалите проекты клиента.']);
        }
        $item->delete();

        return redirect()->route('studio.resources.index', $resource)->with('message', 'Запись удалена.');
    }

    public function publish(string $resource, int $id)
    {
        abort_unless(in_array($resource, ['services', 'cases'], true), 404);
        $item = $this->model($resource)::findOrFail($id);
        Gate::authorize('publish', $item);
        $item->update(['is_published' => ! $item->is_published]);

        return back()->with('message', $item->is_published ? 'Опубликовано.' : 'Снято с публикации.');
    }

    public function settings()
    {
        Gate::authorize('viewAny', SiteSetting::class);

        return view('studio.settings', ['settings' => SiteSetting::all()->pluck('value', 'key')]);
    }

    public function updateSettings(Request $request)
    {
        Gate::authorize('update', SiteSetting::first() ?? new SiteSetting);
        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'],
            'telegram' => ['nullable', 'string', 'max:255'],
            'location_ru' => ['nullable', 'string', 'max:255'], 'location_uz' => ['nullable', 'string', 'max:255'],
        ]);
        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => ['text' => $value]]);
        }

        return back()->with('message', 'Контакты сохранены.');
    }
}

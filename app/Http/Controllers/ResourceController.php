<?php

namespace App\Http\Controllers;

use App\Models\AdditionalService;
use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ResourceController extends Controller
{
    private function model(string $resource): string
    {
        return match ($resource) {
            'clients' => Client::class,
            'projects' => Project::class,
            'tasks' => ProjectTask::class,
            'services' => Service::class,
            'additional_services' => AdditionalService::class,
            'categories' => ServiceCategory::class,
            'technologies' => Technology::class,
            'cases' => CaseStudy::class,
            default => abort(404),
        };
    }

    public function index(string $resource)
    {
        $model = $this->model($resource);
        Gate::authorize('viewAny', $model);

        if (in_array($resource, ['categories', 'technologies'], true)) {
            $items = $resource === 'categories'
                ? ServiceCategory::with('parent')->orderBy('sort_order')->orderBy('id')->get()
                : Technology::orderBy('sort_order')->orderBy('name')->get();

            return view('studio.taxonomy.index', compact('resource', 'items', 'model'));
        }

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
        $categories = ServiceCategory::orderBy('sort_order')->orderBy('id')->get();
        $technologies = Technology::orderBy('sort_order')->orderBy('name')->get();

        if (in_array($resource, ['categories', 'technologies'], true)) {
            $excludedCategoryIds = $item instanceof ServiceCategory && $item->exists ? $item->subtreeIds() : [];

            return view('studio.taxonomy.form', compact('resource', 'item', 'categories', 'excludedCategoryIds'));
        }

        return view('studio.resources.form', [
            'resource' => $resource,
            'item' => $item,
            'clients' => Client::orderBy('name')->get(),
            'projects' => Project::orderBy('title')->get(),
            'staff' => User::orderBy('name')->get(),
            'categories' => $categories,
            'technologies' => $technologies,
            'additionalServices' => $resource === 'services' ? AdditionalService::orderBy('sort_order')->orderBy('id')->get() : collect(),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $model = $this->model($resource);
        Gate::authorize('create', $model);
        $data = $this->validated($request, $resource);
        $technologyIds = $data['technology_ids'] ?? [];
        $additionalServices = array_filter($data['additional_services'] ?? [], fn (array $option): bool => isset($option['id']));
        unset($data['technology_ids'], $data['additional_services']);
        $this->handleImage($request, $resource, $data);
        $item = $model::create($data);
        if (in_array($resource, ['services', 'cases'], true)) {
            $item->technologies()->sync($technologyIds);
        }
        if ($resource === 'services') {
            $this->syncAdditionalServices($item, $additionalServices);
        }

        return redirect()->route('studio.resources.edit', [$resource, $item->id])->with('message', 'Запись создана.');
    }

    public function update(Request $request, string $resource, int $id)
    {
        $item = $this->model($resource)::findOrFail($id);
        Gate::authorize('update', $item);
        $data = $this->validated($request, $resource, $id);
        $technologyIds = $data['technology_ids'] ?? [];
        $additionalServices = array_filter($data['additional_services'] ?? [], fn (array $option): bool => isset($option['id']));
        unset($data['technology_ids'], $data['additional_services']);
        if (array_key_exists('assignee_id', $data) && $data['assignee_id'] != $item->assignee_id) {
            Gate::authorize('assign', $item);
        }
        $oldImagePath = $this->handleImage($request, $resource, $data, $item);
        $item->update($data);
        $this->deleteUploadedImage($oldImagePath);
        if (in_array($resource, ['services', 'cases'], true)) {
            $item->technologies()->sync($technologyIds);
        }
        if ($resource === 'services') {
            $this->syncAdditionalServices($item, $additionalServices);
        }

        return back()->with('message', 'Изменения сохранены.');
    }

    private function handleImage(Request $request, string $resource, array &$data, ?Model $item = null): ?string
    {
        if (! in_array($resource, ['services', 'categories'], true)) {
            return null;
        }

        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('catalog/'.$resource, 'public');
            if ($path === false) {
                throw ValidationException::withMessages(['image' => 'Не удалось сохранить изображение. Попробуйте ещё раз.']);
            }
            $data['image_path'] = '/storage/'.$path;

            return $item?->image_path;
        }

        if ($item && $request->boolean('remove_image')) {
            $data['image_path'] = null;

            return $item->image_path;
        }

        return null;
    }

    private function deleteUploadedImage(?string $path): void
    {
        if ($path && Str::startsWith($path, '/storage/catalog/')) {
            Storage::disk('public')->delete(Str::after($path, '/storage/'));
        }
    }

    private function syncAdditionalServices(Service $service, array $additionalServices): void
    {
        $service->additionalServices()->sync(collect($additionalServices)->mapWithKeys(fn (array $option): array => [
            $option['id'] => ['price' => $option['price'] ?? null, 'sort_order' => $option['sort_order'] ?? 0],
        ])->all());
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
            'categories' => $request->validate([
                'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('service_categories', 'slug')->ignore($id)],
                'title_ru' => ['required', 'string', 'max:255'], 'title_uz' => ['required', 'string', 'max:255'], 'title_en' => ['nullable', 'string', 'max:255'],
                'parent_id' => ['nullable', 'exists:service_categories,id'],
                'sort_order' => ['required', 'integer', 'min:0'],
                'summary_ru' => ['nullable', 'string', 'max:1000'], 'summary_uz' => ['nullable', 'string', 'max:1000'], 'summary_en' => ['nullable', 'string', 'max:1000'],
                'description_ru' => ['nullable', 'string'], 'description_uz' => ['nullable', 'string'], 'description_en' => ['nullable', 'string'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'], 'remove_image' => ['sometimes', 'boolean'],
                'seo_title_ru' => ['nullable', 'string', 'max:255'], 'seo_title_uz' => ['nullable', 'string', 'max:255'], 'seo_title_en' => ['nullable', 'string', 'max:255'],
                'seo_description_ru' => ['nullable', 'string', 'max:500'], 'seo_description_uz' => ['nullable', 'string', 'max:500'], 'seo_description_en' => ['nullable', 'string', 'max:500'],
                'price_from' => ['nullable', 'integer', 'min:0'], 'price_currency' => ['sometimes', Rule::in(['UZS', 'USD', 'EUR', 'RUB'])],
            ]),
            'technologies' => $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('technologies', 'slug')->ignore($id)],
                'kind' => ['required', Rule::in(['frontend', 'backend', 'design', 'devops', 'other'])],
                'sort_order' => ['required', 'integer', 'min:0'],
            ]),
            'additional_services' => $request->validate([
                'title_ru' => ['required', 'string', 'max:255'], 'title_uz' => ['required', 'string', 'max:255'],
                'title_en' => ['required', 'string', 'max:255'],
                'description_ru' => ['nullable', 'string', 'max:2000'], 'description_uz' => ['nullable', 'string', 'max:2000'],
                'description_en' => ['nullable', 'string', 'max:2000'],
                'sort_order' => ['required', 'integer', 'min:0'], 'is_active' => ['sometimes', 'boolean'],
            ]),
            'services', 'cases' => $request->validate([
                'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique($resource === 'cases' ? 'case_studies' : 'services', 'slug')->ignore($id)],
                'title_ru' => ['required', 'string', 'max:255'], 'title_uz' => ['required', 'string', 'max:255'], 'title_en' => ['nullable', 'string', 'max:255'],
                'summary_ru' => ['required', 'string', 'max:1000'], 'summary_uz' => ['required', 'string', 'max:1000'],
                'summary_en' => ['nullable', 'required_with:title_en', 'string', 'max:1000'],
                'description_ru' => ['nullable', 'string'], 'description_uz' => ['nullable', 'string'], 'description_en' => ['nullable', 'string'],
                'seo_title_ru' => ['nullable', 'string', 'max:255'], 'seo_title_uz' => ['nullable', 'string', 'max:255'], 'seo_title_en' => ['nullable', 'string', 'max:255'],
                'seo_description_ru' => ['nullable', 'string', 'max:500'], 'seo_description_uz' => ['nullable', 'string', 'max:500'], 'seo_description_en' => ['nullable', 'string', 'max:500'],
                'sort_order' => ['required', 'integer', 'min:0'],
                'image_path' => ['nullable', 'string', 'max:255'],
                'image' => $resource === 'services' ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'] : ['prohibited'],
                'remove_image' => $resource === 'services' ? ['sometimes', 'boolean'] : ['prohibited'],
                'service_category_id' => $resource === 'services' ? ['nullable', 'exists:service_categories,id'] : ['prohibited'],
                'price_from' => $resource === 'services' ? ['nullable', 'integer', 'min:0'] : ['prohibited'],
                'price_currency' => $resource === 'services' ? ['sometimes', Rule::in(['UZS', 'USD', 'EUR', 'RUB'])] : ['prohibited'],
                'technology_ids' => ['sometimes', 'array'],
                'technology_ids.*' => ['integer', 'distinct', 'exists:technologies,id'],
                'additional_services' => $resource === 'services' ? ['sometimes', 'array'] : ['prohibited'],
                'additional_services.*.id' => $resource === 'services' ? ['nullable', 'integer', 'distinct', 'exists:additional_services,id'] : ['prohibited'],
                'additional_services.*.price' => $resource === 'services' ? ['nullable', 'integer', 'min:0'] : ['prohibited'],
                'additional_services.*.sort_order' => $resource === 'services' ? ['required', 'integer', 'min:0'] : ['prohibited'],
            ]),
        };
        if ($resource === 'additional_services') {
            $data['title'] = ['ru' => $data['title_ru'], 'uz' => $data['title_uz'], 'en' => $data['title_en']];
            $data['description'] = array_filter(['ru' => $data['description_ru'] ?? null, 'uz' => $data['description_uz'] ?? null, 'en' => $data['description_en'] ?? null], fn ($value) => filled($value));
            $data['is_active'] = $request->boolean('is_active');
            foreach (['title_ru', 'title_uz', 'title_en', 'description_ru', 'description_uz', 'description_en'] as $field) {
                unset($data[$field]);
            }
        }
        if ($resource === 'categories') {
            if ($id !== null && ($data['parent_id'] ?? null) !== null && in_array((int) $data['parent_id'], ServiceCategory::findOrFail($id)->subtreeIds(), true)) {
                throw ValidationException::withMessages(['parent_id' => 'Нельзя выбрать эту категорию родительской.']);
            }
            $data['title'] = array_filter(['ru' => $data['title_ru'], 'uz' => $data['title_uz'], 'en' => $data['title_en'] ?? null], fn ($value) => filled($value));
            foreach (['summary', 'description', 'seo_title', 'seo_description'] as $field) {
                $data[$field] = array_filter(['ru' => $data[$field.'_ru'] ?? null, 'uz' => $data[$field.'_uz'] ?? null, 'en' => $data[$field.'_en'] ?? null], fn ($value) => filled($value));
                foreach (['ru', 'uz', 'en'] as $locale) {
                    unset($data[$field.'_'.$locale]);
                }
            }
            unset($data['title_ru'], $data['title_uz'], $data['title_en']);
        }
        if (in_array($resource, ['services', 'cases'], true)) {
            $data['title'] = array_filter(['ru' => $data['title_ru'], 'uz' => $data['title_uz'], 'en' => $data['title_en'] ?? null], fn ($value) => filled($value));
            $data['summary'] = array_filter(['ru' => $data['summary_ru'], 'uz' => $data['summary_uz'], 'en' => $data['summary_en'] ?? null], fn ($value) => filled($value));
            $data['description'] = array_filter(['ru' => $data['description_ru'] ?? null, 'uz' => $data['description_uz'] ?? null, 'en' => $data['description_en'] ?? null], fn ($value) => filled($value));
            $data['seo_title'] = array_filter(['ru' => $data['seo_title_ru'] ?? null, 'uz' => $data['seo_title_uz'] ?? null, 'en' => $data['seo_title_en'] ?? null], fn ($value) => filled($value));
            $data['seo_description'] = array_filter(['ru' => $data['seo_description_ru'] ?? null, 'uz' => $data['seo_description_uz'] ?? null, 'en' => $data['seo_description_en'] ?? null], fn ($value) => filled($value));
            unset($data['title_ru'], $data['title_uz'], $data['title_en'], $data['summary_ru'], $data['summary_uz'], $data['summary_en']);
            foreach (['ru', 'uz', 'en'] as $locale) {
                unset($data['description_'.$locale], $data['seo_title_'.$locale], $data['seo_description_'.$locale]);
            }
            if ($resource === 'services') {
                unset($data['image_path']);
            } else {
                unset($data['service_category_id'], $data['price_from'], $data['price_currency']);
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
        if ($item instanceof ServiceCategory && ($item->children()->exists() || $item->services()->exists())) {
            return back()->withErrors(['category' => 'Переместите дочерние категории и услуги перед удалением.']);
        }
        if ($item instanceof Technology && ($item->services()->exists() || $item->caseStudies()->exists())) {
            return back()->withErrors(['technology' => 'Сначала уберите технологию из услуг и кейсов.']);
        }
        $imagePath = $item instanceof Service || $item instanceof ServiceCategory ? $item->image_path : null;
        $item->delete();
        $this->deleteUploadedImage($imagePath);

        return redirect()->route('studio.resources.index', $resource)->with('message', 'Запись удалена.');
    }

    public function publish(string $resource, int $id)
    {
        abort_unless(in_array($resource, ['services', 'categories', 'cases'], true), 404);
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
            'location_ru' => ['nullable', 'string', 'max:255'], 'location_uz' => ['nullable', 'string', 'max:255'], 'location_en' => ['nullable', 'string', 'max:255'],
            'home_seo_ru' => ['nullable', 'string', 'max:5000'], 'home_seo_uz' => ['nullable', 'string', 'max:5000'], 'home_seo_en' => ['nullable', 'string', 'max:5000'],
        ]);
        foreach ($data as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => ['text' => $value]]);
        }

        return back()->with('message', 'Контакты сохранены.');
    }
}

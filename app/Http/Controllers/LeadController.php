<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function storePublic(Request $request, string $locale)
    {
        abort_unless(in_array($locale, ['ru', 'uz', 'en'], true), 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:255'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:50'],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_published', true)],
            'service_category_id' => ['nullable', Rule::exists('service_categories', 'id')->where('is_published', true)],
            'return_page' => ['nullable', Rule::in(['service', 'category'])],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'size:0'],
        ]);
        unset($data['website']);
        $returnPage = $data['return_page'] ?? null;
        unset($data['return_page']);
        Lead::create($data + ['locale' => $locale]);

        if ($returnPage === 'service' && ! empty($data['service_id'])) {
            $service = Service::findOrFail($data['service_id']);

            return redirect()->route('site.detail', [$locale, 'services', $service->slug])->with('success', true);
        }
        if ($returnPage === 'category' && ! empty($data['service_category_id'])) {
            $category = ServiceCategory::findOrFail($data['service_category_id']);

            return redirect()->route('site.category', [$locale, $category->slug])->with('success', true);
        }

        return redirect()->route('site.contact', $locale)->with('success', true);
    }

    public function index()
    {
        Gate::authorize('viewAny', Lead::class);

        return view('studio.leads', ['leads' => Lead::with(['service', 'category', 'assignee'])->latest()->paginate(20)]);
    }

    public function show(Lead $lead)
    {
        Gate::authorize('view', $lead);

        return view('studio.lead', ['lead' => $lead->load(['service', 'category', 'assignee', 'client']), 'staff' => User::orderBy('name')->get()]);
    }

    public function update(Request $request, Lead $lead)
    {
        Gate::authorize('update', $lead);
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'qualified', 'won', 'lost'])],
            'assignee_id' => ['nullable', 'exists:users,id'],
        ]);
        if (($data['assignee_id'] ?? null) != $lead->assignee_id) {
            Gate::authorize('assign', $lead);
        }
        $lead->update($data);

        return back()->with('message', 'Заявка обновлена.');
    }

    public function convert(Request $request, Lead $lead)
    {
        Gate::authorize('update', $lead);
        Gate::authorize('create', Client::class);
        Gate::authorize('create', Project::class);
        abort_if($lead->client_id !== null || Project::where('lead_id', $lead->id)->exists(), 409);
        $data = $request->validate(['title' => ['required', 'string', 'max:255']]);

        DB::transaction(function () use ($lead, $data): void {
            $client = Client::create(['name' => $lead->name, 'email' => $lead->email, 'phone' => $lead->phone]);
            Project::create(['client_id' => $client->id, 'lead_id' => $lead->id, 'title' => $data['title'], 'description' => $lead->message]);
            $lead->update(['client_id' => $client->id, 'status' => 'won']);
        });

        return redirect()->route('studio.resources.index', 'projects')->with('message', 'Клиент и проект созданы.');
    }

    public function destroy(Lead $lead)
    {
        Gate::authorize('delete', $lead);
        $lead->delete();

        return redirect()->route('studio.leads.index')->with('message', 'Заявка удалена.');
    }
}

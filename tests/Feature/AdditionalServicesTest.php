<?php

namespace Tests\Feature;

use App\Models\AdditionalService;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdditionalServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_options_can_have_different_prices_per_service_and_quote_is_snapshotted(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::factory()->create(['is_owner' => true]);
        $first = Service::where('slug', 'landing-page')->firstOrFail();
        $second = Service::where('slug', 'custom-crm')->firstOrFail();
        $option = $this->option();
        $outputLevel = ob_get_level();
        $this->actingAs($owner)->put("/studio/services/{$first->id}", $this->serviceData($first, $option->id, 500000))->assertRedirect();
        $this->put("/studio/services/{$second->id}", $this->serviceData($second, $option->id, 2000000))->assertRedirect();

        $this->assertSame(500000, (int) $first->fresh()->additionalServices->first()->pivot->price);
        $this->assertSame(2000000, (int) $second->fresh()->additionalServices->first()->pivot->price);
        foreach (['ru', 'uz', 'en'] as $locale) {
            $this->get("/{$locale}/services/landing-page")->assertOk()->assertSee('data-service-calculator')->assertSee($option->title[$locale]);
            $this->assertSame($outputLevel, ob_get_level(), "Output buffer leaked on {$locale} service page");
        }

        $this->post('/ru/contact', $this->leadData($first, [$option->id]))->assertRedirect();
        $this->assertSame($outputLevel, ob_get_level(), 'Output buffer leaked on lead submission');
        $lead = Lead::firstOrFail();
        $this->assertSame($first->price_from + 500000, $lead->quote_snapshot['total']);
        $this->assertSame(500000, $lead->quote_snapshot['options'][0]['price']);
        $option->update(['title' => ['ru' => 'Другое', 'uz' => 'Boshqa', 'en' => 'Other']]);
        $first->additionalServices()->updateExistingPivot($option->id, ['price' => 900000]);
        $this->assertSame('Хостинг', $lead->fresh()->quote_snapshot['options'][0]['title']);
        $this->assertSame(500000, $lead->fresh()->quote_snapshot['options'][0]['price']);
        $this->get("/studio/leads/{$lead->id}")->assertOk()->assertSee('Хостинг')->assertSee('500 000');
        $this->assertSame($outputLevel, ob_get_level(), 'Output buffer leaked on CRM lead page');
    }

    public function test_unpriced_option_requires_individual_quote_and_invalid_option_is_rejected(): void
    {
        $this->seed(DatabaseSeeder::class);
        $service = Service::where('slug', 'landing-page')->firstOrFail();
        $other = Service::where('slug', 'custom-crm')->firstOrFail();
        $option = $this->option();
        $service->additionalServices()->attach($option->id, ['price' => null]);
        $this->post('/en/contact', $this->leadData($service, [$option->id]))->assertRedirect();
        $this->assertNull(Lead::firstOrFail()->quote_snapshot['total']);

        $this->post('/en/contact', $this->leadData($other, [$option->id]))->assertSessionHasErrors('additional_service_ids');
        $option->update(['is_active' => false]);
        $this->post('/en/contact', $this->leadData($service, [$option->id]))->assertSessionHasErrors('additional_service_ids');
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_crm_permissions_protect_additional_services(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->get('/studio/additional_services')->assertRedirect('/login');
        $role = Role::create(['name' => 'Viewer']);
        $role->permissions()->attach(Permission::where('resource', 'additional_services')->where('action', 'view')->firstOrFail());
        $viewer = User::factory()->create(['role_id' => $role->id]);
        $this->actingAs($viewer)->get('/studio/additional_services')->assertOk();
        $this->post('/studio/additional_services', $this->optionData())->assertForbidden();
        $owner = User::factory()->create(['is_owner' => true]);
        $this->actingAs($owner)->post('/studio/additional_services', $this->optionData())->assertRedirect();
        $this->assertSame(1, AdditionalService::where('title->en', 'Hosting')->count());
    }

    private function option(): AdditionalService
    {
        return AdditionalService::factory()->create(['title' => ['ru' => 'Хостинг', 'uz' => 'Xosting', 'en' => 'Hosting']]);
    }

    private function optionData(): array
    {
        return ['title_ru' => 'Хостинг', 'title_uz' => 'Xosting', 'title_en' => 'Hosting', 'sort_order' => 0, 'is_active' => '1'];
    }

    private function serviceData(Service $service, int $optionId, ?int $price): array
    {
        return [
            'slug' => $service->slug, 'title_ru' => $service->title['ru'], 'title_uz' => $service->title['uz'],
            'title_en' => $service->title['en'] ?? null,
            'summary_ru' => $service->summary['ru'], 'summary_uz' => $service->summary['uz'],
            'summary_en' => $service->summary['en'] ?? null,
            'sort_order' => $service->sort_order, 'price_from' => $service->price_from,
            'price_currency' => $service->price_currency,
            'additional_services' => [$optionId => ['id' => $optionId, 'price' => $price, 'sort_order' => 0]],
        ];
    }

    private function leadData(Service $service, array $optionIds): array
    {
        return [
            'name' => 'Customer', 'email' => 'customer@example.test', 'message' => 'Please build this service for our team.',
            'service_id' => $service->id, 'return_page' => 'service', 'additional_service_ids' => $optionIds,
        ];
    }
}

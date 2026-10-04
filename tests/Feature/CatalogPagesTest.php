<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_seeder_is_repeatable_and_keeps_owner_edits(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(6, ServiceCategory::count());
        $this->assertSame(13, Service::count());
        $crm = ServiceCategory::where('slug', 'crm-systems')->firstOrFail();
        $this->assertSame('business-systems', $crm->parent->slug);
        $service = Service::where('slug', 'custom-crm')->firstOrFail();
        $this->assertSame($crm->id, $service->service_category_id);
        $this->assertSame(25000000, $service->price_from);
        $this->assertFalse($service->is_published);

        $service->update(['price_from' => 30000000]);
        $this->seed(CatalogSeeder::class);
        $this->assertSame(13, Service::count());
        $this->assertSame(30000000, $service->fresh()->price_from);
    }

    public function test_category_and_service_pages_show_price_seo_and_contextual_lead_form(): void
    {
        $this->seed(DatabaseSeeder::class);
        $category = ServiceCategory::where('slug', 'websites')->firstOrFail();
        $service = Service::where('slug', 'landing-page')->firstOrFail();
        $this->get('/ru/service-categories/websites')->assertNotFound();
        $this->get('/ru/services/landing-page')->assertNotFound();
        $this->actingAs(User::factory()->create(['is_owner' => true]));
        $this->post("/studio/categories/{$category->id}/publish")->assertRedirect();
        $this->post("/studio/services/{$service->id}/publish")->assertRedirect();

        $this->get('/ru/service-categories/websites')->assertOk()
            ->assertSee('Разработка сайтов')->assertSee('5 000 000')->assertSee('name="service_category_id"', false)
            ->assertSee('<title>Разработка сайтов | Smartnet</title>', false);
        $this->get('/en/service-categories/websites')->assertOk()->assertSee('Website development');
        $this->get('/ru/services/landing-page')->assertOk()
            ->assertSee('Лендинг')->assertSee('5 000 000')->assertSee('name="service_id"', false);
        $this->get('/en/services/landing-page')->assertOk()->assertSee('Landing page');
        $this->get('/sitemap.xml')->assertSee('/ru/service-categories/websites')->assertSee('/ru/services/landing-page');

        $this->post('/ru/contact', [
            'name' => 'Иван', 'email' => 'ivan@example.test', 'message' => 'Нужна страница для продукта',
            'service_category_id' => $category->id, 'return_page' => 'category',
        ])->assertRedirect('/ru/service-categories/websites');
        $this->assertSame($category->id, Lead::firstOrFail()->service_category_id);
        $this->post('/en/contact', [
            'name' => 'Jane', 'email' => 'jane@example.test', 'message' => 'We need a landing page.',
            'service_id' => $service->id, 'return_page' => 'service',
        ])->assertRedirect('/en/services/landing-page');
        $this->assertSame($service->id, Lead::orderByDesc('id')->firstOrFail()->service_id);
    }

    public function test_owner_can_change_prices_and_category_seo_in_crm(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::factory()->create(['is_owner' => true]));
        $category = ServiceCategory::where('slug', 'telegram-bots')->firstOrFail();
        $this->put("/studio/categories/{$category->id}", [
            'slug' => 'telegram-bots', 'title_ru' => 'Telegram-боты', 'title_uz' => 'Telegram botlar', 'title_en' => 'Telegram bots',
            'summary_ru' => 'Боты для бизнеса', 'summary_uz' => 'Biznes uchun botlar', 'summary_en' => 'Bots for business',
            'seo_title_ru' => 'Боты для бизнеса | Smartnet', 'price_from' => 9000000, 'price_currency' => 'UZS', 'sort_order' => 1,
        ])->assertRedirect();
        $this->assertSame(9000000, $category->fresh()->price_from);
        $this->assertSame('Боты для бизнеса | Smartnet', $category->fresh()->seo_title['ru']);
    }
}

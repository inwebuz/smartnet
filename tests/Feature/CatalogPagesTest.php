<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SiteSetting;
use App\Models\Technology;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $this->assertTrue($service->is_published);
        $this->assertSame('/images/services/custom-crm.png', $service->image_path);
        $this->assertTrue($crm->is_published);
        $this->assertSame(14, Technology::count());
        $this->assertSame('info@smartnet.uz', SiteSetting::where('key', 'email')->firstOrFail()->value['text']);
        $this->assertSame('+998 33 901-03-33', SiteSetting::where('key', 'phone')->firstOrFail()->value['text']);
        $this->assertSame('@uzsmweb', SiteSetting::where('key', 'telegram')->firstOrFail()->value['text']);

        $service->update(['price_from' => 30000000]);
        $service->update(['image_path' => '/storage/catalog/services/custom.png']);
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(13, Service::count());
        $this->assertSame(14, Technology::count());
        $this->assertSame(30000000, $service->fresh()->price_from);
        $this->assertSame('/storage/catalog/services/custom.png', $service->fresh()->image_path);
    }

    public function test_owner_can_upload_replace_and_remove_service_and_category_images(): void
    {
        Storage::fake('public');
        $this->seed(DatabaseSeeder::class);
        $owner = User::factory()->create(['is_owner' => true]);
        $service = Service::where('slug', 'landing-page')->firstOrFail();
        $category = ServiceCategory::where('slug', 'websites')->firstOrFail();

        $imageContents = file_get_contents(public_path('images/services/landing-page.png'));
        $this->put("/studio/services/{$service->id}", ['image' => UploadedFile::fake()->createWithContent('cover.png', $imageContents)])->assertRedirect();
        $this->assertSame('/images/services/landing-page.png', $service->fresh()->image_path);

        $this->actingAs($owner);
        $this->put("/studio/services/{$service->id}", $this->serviceData($service, ['image' => UploadedFile::fake()->createWithContent('cover.png', $imageContents)]))->assertRedirect();
        $firstPath = $service->fresh()->image_path;
        Storage::disk('public')->assertExists(substr($firstPath, strlen('/storage/')));
        $this->put("/studio/services/{$service->id}", $this->serviceData($service, ['image' => UploadedFile::fake()->createWithContent('replacement.png', $imageContents)]))->assertRedirect();
        Storage::disk('public')->assertMissing(substr($firstPath, strlen('/storage/')));
        $this->put("/studio/services/{$service->id}", $this->serviceData($service, ['remove_image' => '1']))->assertRedirect();
        $this->assertNull($service->fresh()->image_path);

        $this->put("/studio/categories/{$category->id}", $this->categoryData($category, ['image' => UploadedFile::fake()->createWithContent('category.png', $imageContents)]))->assertRedirect();
        $this->assertStringStartsWith('/storage/catalog/categories/', $category->fresh()->image_path);
        $this->get('/ru/service-categories/websites')->assertOk()->assertSee($category->fresh()->image_path);
        $this->put("/studio/categories/{$category->id}", $this->categoryData($category, ['image' => UploadedFile::fake()->create('bad.txt', 1, 'text/plain')]))->assertSessionHasErrors('image');
    }

    private function serviceData(Service $service, array $extra = []): array
    {
        return array_merge([
            'slug' => $service->slug, 'title_ru' => $service->title['ru'], 'title_uz' => $service->title['uz'],
            'summary_ru' => $service->summary['ru'], 'summary_uz' => $service->summary['uz'], 'sort_order' => $service->sort_order,
        ], $extra);
    }

    private function categoryData(ServiceCategory $category, array $extra = []): array
    {
        return array_merge([
            'slug' => $category->slug, 'title_ru' => $category->title['ru'], 'title_uz' => $category->title['uz'],
            'sort_order' => $category->sort_order,
        ], $extra);
    }

    public function test_category_and_service_pages_show_price_seo_and_contextual_lead_form(): void
    {
        $this->seed(DatabaseSeeder::class);
        $category = ServiceCategory::where('slug', 'websites')->firstOrFail();
        $service = Service::where('slug', 'landing-page')->firstOrFail();
        $this->get('/ru/service-categories/websites')->assertOk()
            ->assertSee('Разработка сайтов')->assertSee('5 000 000')->assertSee('name="service_category_id"', false)
            ->assertSee('<title>Разработка сайтов | Smartnet</title>', false);
        $this->get('/en/service-categories/websites')->assertOk()->assertSee('Website development');
        $this->get('/ru/services/landing-page')->assertOk()
            ->assertSee('Лендинг')->assertSee('5 000 000')->assertSee('name="service_id"', false)
            ->assertSee('/images/services/landing-page.png');
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

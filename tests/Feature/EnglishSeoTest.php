<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnglishSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_pages_and_contact_request_work(): void
    {
        $this->get('/en')->assertOk()->assertSee('Digital experiences built to work for you.')->assertSee('hreflang="ru"', false);
        $this->get('/en/services')->assertOk()->assertSee('Our services');
        $this->get('/en/cases')->assertOk()->assertSee('Our work');
        $this->get('/en/contact')->assertOk()->assertSee('Send request');
        $this->post('/en/contact', ['name' => 'Jane', 'email' => 'jane@example.test', 'message' => 'We need a new website.'])
            ->assertRedirect('/en/contact');
        $this->assertSame('en', Lead::firstOrFail()->locale);
    }

    public function test_english_content_and_seo_fields_are_managed_in_crm(): void
    {
        $this->seed(DatabaseSeeder::class);
        $service = Service::where('slug', 'web-development')->firstOrFail();
        $service->update(['is_published' => true]);
        $this->get('/en/services')->assertDontSee('web-development');
        $this->get('/en/services/web-development')->assertNotFound();

        $this->actingAs(User::factory()->create(['is_owner' => true]));
        $this->put("/studio/services/{$service->id}", [
            'slug' => 'web-development', 'title_ru' => 'Сайты', 'title_uz' => 'Saytlar', 'title_en' => 'Web development',
            'summary_ru' => 'Создание сайтов', 'summary_uz' => 'Saytlar yaratish', 'summary_en' => 'Websites that work.',
            'seo_title_en' => 'Custom websites | Smartnet', 'seo_description_en' => 'Custom websites for growing companies.',
            'sort_order' => 0,
        ])->assertRedirect();
        $this->get('/en/services/web-development')->assertOk()
            ->assertSee('<title>Custom websites | Smartnet</title>', false)
            ->assertSee('content="Custom websites for growing companies."', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('hreflang="en"', false);
    }

    public function test_sitemap_contains_only_published_pages_and_translated_english_details(): void
    {
        $this->seed(DatabaseSeeder::class);
        $response = $this->get('/sitemap.xml')->assertOk()->assertSee('/en/services')->assertDontSee('/ru/services/web-development');
        $this->assertNotFalse(simplexml_load_string($response->getContent()));
        Service::where('slug', 'web-development')->firstOrFail()->update(['is_published' => true]);
        $this->get('/sitemap.xml')->assertSee('/ru/services/web-development')->assertDontSee('/en/services/web-development');
    }
}

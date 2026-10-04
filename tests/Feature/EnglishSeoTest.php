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
        $this->get('/en')->assertOk()->assertSee('Web-sites, telegram bots, and other systems development')->assertSee('hreflang="ru"', false);
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
        $service = Service::where('slug', 'corporate-website')->firstOrFail();
        $service->update(['is_published' => true, 'title' => ['ru' => 'Сайты', 'uz' => 'Saytlar']]);
        $this->get('/en/services')->assertDontSee('corporate-website');
        $this->get('/en/services/corporate-website')->assertNotFound();

        $this->actingAs(User::factory()->create(['is_owner' => true]));
        $this->put("/studio/services/{$service->id}", [
            'slug' => 'corporate-website', 'title_ru' => 'Сайты', 'title_uz' => 'Saytlar', 'title_en' => 'Web development',
            'summary_ru' => 'Создание сайтов', 'summary_uz' => 'Saytlar yaratish', 'summary_en' => 'Websites that work.',
            'seo_title_en' => 'Custom websites | Smartnet', 'seo_description_en' => 'Custom websites for growing companies.',
            'sort_order' => 0,
        ])->assertRedirect();
        $this->get('/en/services/corporate-website')->assertOk()
            ->assertSee('<title>Custom websites | Smartnet</title>', false)
            ->assertSee('content="Custom websites for growing companies."', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee('hreflang="en"', false);
    }

    public function test_sitemap_contains_only_published_pages_and_translated_english_details(): void
    {
        $this->seed(DatabaseSeeder::class);
        $response = $this->get('/sitemap.xml')->assertOk()->assertSee('/en/services')->assertDontSee('/ru/services/corporate-website');
        $this->assertNotFalse(simplexml_load_string($response->getContent()));
        Service::where('slug', 'corporate-website')->firstOrFail()->update(['is_published' => true, 'title' => ['ru' => 'Сайты', 'uz' => 'Saytlar']]);
        $this->get('/sitemap.xml')->assertSee('/ru/services/corporate-website')->assertDontSee('/en/services/corporate-website');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Services\TelegramLeadService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class TelegramLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_forms_require_phone_and_have_no_email_field(): void
    {
        $this->seed(DatabaseSeeder::class);
        foreach (['ru', 'uz', 'en'] as $locale) {
            foreach (['contact', 'services/landing-page', 'service-categories/websites'] as $page) {
                $this->get("/{$locale}/{$page}")->assertOk()->assertDontSee('name="email"', false)->assertSee('type="tel"', false);
            }
        }
        $this->post('/uz/contact', ['name' => 'Ali', 'email' => 'ali@example.test', 'message' => 'Yangi sayt kerak'])->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_saved_lead_is_sent_immediately_and_invalid_form_does_not_send(): void
    {
        $this->configureTelegram();
        Http::preventStrayRequests();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $this->post('/uz/contact', $this->leadData())->assertRedirect('/uz/contact');
        $lead = Lead::firstOrFail();
        $this->assertNull($lead->email);
        Http::assertSent(fn ($request) => str_contains($request['text'], '№'.$lead->id));
        $this->post('/uz/contact', array_merge($this->leadData(), ['website' => 'spam']))->assertSessionHasErrors('website');
        Http::assertSentCount(1);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_unconfigured_telegram_keeps_the_lead_in_crm(): void
    {
        Http::fake();
        config(['services.telegram.bot_token' => null, 'services.telegram.chat_id' => null]);
        $this->post('/uz/contact', $this->leadData())->assertRedirect();
        $this->assertDatabaseCount('leads', 1);
        Http::assertNothingSent();
    }

    public function test_notification_contains_contacts_quote_and_full_long_message(): void
    {
        $this->configureTelegram();
        Http::preventStrayRequests();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        $lead = Lead::create($this->leadData() + [
            'locale' => 'uz',
            'quote_snapshot' => ['service_title' => 'Sayt', 'base_price' => 5000000, 'currency' => 'UZS', 'options' => [
                ['id' => 1, 'title' => 'Xosting', 'price' => 100000],
            ], 'total' => 5100000],
        ]);
        $lead->update(['message' => str_repeat('Salom 👋 ', 500)]);
        app(TelegramLeadService::class)->send($lead);
        $text = Http::recorded()->map(fn ($pair) => $pair[0]['text'])->implode('');
        $this->assertStringContainsString($lead->phone, $text);
        $this->assertStringContainsString('Xosting: 100 000 UZS', $text);
        $this->assertStringContainsString('5 100 000 UZS', $text);
        $this->assertStringContainsString(route('studio.leads.show', $lead), $text);
        $this->assertStringContainsString($lead->message, $text);
        foreach (Http::recorded() as [$request]) {
            $this->assertSame('-1001234567890', $request['chat_id']);
            $this->assertLessThanOrEqual(4096, strlen(mb_convert_encoding($request['text'], 'UTF-16LE', 'UTF-8')) / 2);
            $this->assertArrayNotHasKey('parse_mode', $request->data());
        }
    }

    public function test_telegram_failure_keeps_saved_lead_and_does_not_expose_token(): void
    {
        $this->configureTelegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false], 403)]);
        $lead = Lead::create($this->leadData() + ['locale' => 'uz']);
        try {
            app(TelegramLeadService::class)->send($lead);
            $this->fail('Telegram failure should be reported.');
        } catch (RuntimeException $exception) {
            $this->assertStringNotContainsString('test-token', $exception->getMessage());
            $this->assertStringContainsString('403', $exception->getMessage());
        }
        $this->assertDatabaseCount('leads', 1);
    }

    public function test_telegram_failure_does_not_break_public_form_submission(): void
    {
        $this->configureTelegram();
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false], 500)]);
        $this->post('/uz/contact', $this->leadData())->assertRedirect('/uz/contact')->assertSessionHas('success', true);
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('jobs', 0);
        Http::assertSentCount(1);
    }

    private function configureTelegram(): void
    {
        config(['services.telegram.bot_token' => 'test-token', 'services.telegram.chat_id' => '-1001234567890']);
    }

    private function leadData(): array
    {
        return ['name' => 'Ali', 'phone' => '+998339010333', 'message' => 'Hi'];
    }
}

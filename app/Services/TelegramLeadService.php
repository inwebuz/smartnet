<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramLeadService
{
    public function send(Lead $lead): void
    {
        $lead->loadMissing(['service', 'category']);

        $token = config('services.telegram.bot_token');
        $chatId = config('services.telegram.chat_id');
        if (blank($token) || blank($chatId)) {
            throw new RuntimeException('Telegram notification configuration is missing.');
        }

        foreach (mb_str_split($this->message($lead), 1800) as $part) {
            try {
                $response = Http::asJson()->connectTimeout(3)->timeout(8)
                    ->post('https://api.telegram.org/bot'.$token.'/sendMessage', [
                        'chat_id' => $chatId,
                        'text' => $part,
                        'link_preview_options' => ['is_disabled' => true],
                    ]);
            } catch (ConnectionException) {
                throw new RuntimeException('Unable to connect to Telegram.');
            }

            if (! $response->successful() || $response->json('ok') !== true) {
                throw new RuntimeException('Telegram rejected the notification (HTTP '.$response->status().').');
            }
        }
    }

    private function message(Lead $lead): string
    {
        $quote = $lead->quote_snapshot;
        $title = $quote['service_title'] ?? $lead->service?->title[$lead->locale] ?? $lead->category?->title[$lead->locale] ?? 'Не выбрана';
        $lines = [
            'Новая заявка Smartnet №'.$lead->id,
            'Имя: '.$lead->name,
            'Телефон: '.$lead->phone,
            'Язык: '.strtoupper($lead->locale),
            'Услуга / категория: '.$title,
        ];

        if ($quote !== null) {
            $lines[] = 'Базовая стоимость: '.$this->price($quote['base_price'], $quote['currency']);
            foreach ($quote['options'] as $option) {
                $lines[] = '+ '.$option['title'].': '.$this->price($option['price'], $quote['currency']);
            }
            $lines[] = 'Предварительная стоимость: '.$this->price($quote['total'], $quote['currency']);
        }

        $lines[] = 'CRM: '.route('studio.leads.show', $lead);
        $lines[] = '';
        $lines[] = 'Сообщение:';
        $lines[] = $lead->message;

        return implode("\n", $lines);
    }

    private function price(?int $amount, string $currency): string
    {
        return $amount === null ? 'по запросу' : number_format($amount, 0, '.', ' ').' '.$currency;
    }
}

<?php

namespace Database\Seeders;

use App\Models\AdditionalService;
use Illuminate\Database\Seeder;

class AdditionalServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            ['ru' => 'Техническая поддержка', 'uz' => 'Texnik yordam', 'en' => 'Technical support'],
            ['ru' => 'Дополнительный язык', 'uz' => 'Qo‘shimcha til', 'en' => 'Additional language'],
            ['ru' => 'Аналитика и отчёты', 'uz' => 'Tahlil va hisobotlar', 'en' => 'Analytics and reports'],
        ] as $title) {
            AdditionalService::firstOrCreate(['title->en' => $title['en']], [
                'title' => $title,
                'is_active' => false,
            ]);
        }
    }
}

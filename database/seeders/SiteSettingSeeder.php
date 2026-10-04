<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'email' => 'info@smartnet.uz',
            'phone' => '+998 33 901-03-33',
            'telegram' => '@uzsmweb',
        ] as $key => $text) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => ['text' => $text]]);
        }
    }
}

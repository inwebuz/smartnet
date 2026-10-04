<?php

namespace Database\Seeders;

use App\Models\Technology;
use Illuminate\Database\Seeder;

class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        $technologies = [
            ['PHP', 'php', 'backend'],
            ['Laravel', 'laravel', 'backend'],
            ['Node.js', 'nodejs', 'backend'],
            ['JavaScript', 'javascript', 'frontend'],
            ['TypeScript', 'typescript', 'frontend'],
            ['Vue.js', 'vuejs', 'frontend'],
            ['React', 'react', 'frontend'],
            ['Tailwind CSS', 'tailwind-css', 'frontend'],
            ['MariaDB', 'mariadb', 'backend'],
            ['PostgreSQL', 'postgresql', 'backend'],
            ['Redis', 'redis', 'backend'],
            ['Telegram Bot API', 'telegram-bot-api', 'other'],
            ['Docker', 'docker', 'devops'],
            ['Figma', 'figma', 'design'],
        ];

        foreach ($technologies as $order => [$name, $slug, $kind]) {
            Technology::firstOrCreate(['slug' => $slug], [
                'name' => $name,
                'kind' => $kind,
                'sort_order' => $order,
            ]);
        }
    }
}

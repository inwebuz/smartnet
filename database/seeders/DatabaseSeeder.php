<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\Permission;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (['leads', 'clients', 'projects', 'tasks', 'services', 'categories', 'technologies', 'cases', 'team', 'roles', 'settings'] as $resource) {
            foreach (['view', 'create', 'update', 'delete'] as $action) {
                Permission::firstOrCreate(compact('resource', 'action'));
            }
            if (in_array($resource, ['services', 'cases'], true)) {
                Permission::firstOrCreate(['resource' => $resource, 'action' => 'publish']);
            }
            if (in_array($resource, ['leads', 'projects', 'tasks'], true)) {
                Permission::firstOrCreate(['resource' => $resource, 'action' => 'assign']);
            }
        }
        Service::firstOrCreate(['slug' => 'web-development'], [
            'title' => ['ru' => 'Разработка сайтов', 'uz' => 'Veb-saytlar yaratish'],
            'summary' => ['ru' => 'Сайты, которые помогают бизнесу расти.', 'uz' => 'Biznesingiz o‘sishiga yordam beradigan saytlar.'],
            'is_published' => false,
        ]);
        Service::firstOrCreate(['slug' => 'branding'], [
            'title' => ['ru' => 'Дизайн и брендинг', 'uz' => 'Dizayn va brending'],
            'summary' => ['ru' => 'Ясный визуальный язык для вашего продукта.', 'uz' => 'Mahsulotingiz uchun aniq vizual til.'],
            'is_published' => false,
        ]);
        CaseStudy::firstOrCreate(['slug' => 'your-first-project'], [
            'title' => ['ru' => 'Ваш первый кейс', 'uz' => 'Birinchi loyihangiz'],
            'summary' => ['ru' => 'Замените этот черновик реальным проектом.', 'uz' => 'Bu qoralamani haqiqiy loyiha bilan almashtiring.'],
            'is_published' => false,
        ]);
    }
}

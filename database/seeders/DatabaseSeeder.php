<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\Permission;
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
            if (in_array($resource, ['services', 'categories', 'cases'], true)) {
                Permission::firstOrCreate(['resource' => $resource, 'action' => 'publish']);
            }
            if (in_array($resource, ['leads', 'projects', 'tasks'], true)) {
                Permission::firstOrCreate(['resource' => $resource, 'action' => 'assign']);
            }
        }
        $this->call(CatalogSeeder::class);
        CaseStudy::firstOrCreate(['slug' => 'your-first-project'], [
            'title' => ['ru' => 'Ваш первый кейс', 'uz' => 'Birinchi loyihangiz'],
            'summary' => ['ru' => 'Замените этот черновик реальным проектом.', 'uz' => 'Bu qoralamani haqiqiy loyiha bilan almashtiring.'],
            'is_published' => false,
        ]);
    }
}

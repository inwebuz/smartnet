<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Technology;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    public function test_nested_categories_filter_published_services_and_show_technology_stack(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::factory()->create(['is_owner' => true]);
        $this->actingAs($owner);

        $this->post('/studio/categories', ['slug' => 'web', 'title_ru' => 'Веб', 'title_uz' => 'Veb', 'sort_order' => 0])->assertRedirect();
        $root = ServiceCategory::where('slug', 'web')->firstOrFail();
        $this->post('/studio/categories', ['slug' => 'shops', 'title_ru' => 'Магазины', 'title_uz' => 'Do‘konlar', 'parent_id' => $root->id, 'sort_order' => 0])->assertRedirect();
        $child = ServiceCategory::where('slug', 'shops')->firstOrFail();
        $this->post('/studio/technologies', ['name' => 'Laravel', 'slug' => 'laravel', 'kind' => 'backend', 'sort_order' => 0])->assertRedirect();
        $technology = Technology::firstOrFail();
        $this->post('/studio/services', [
            'slug' => 'shop', 'title_ru' => 'Интернет-магазин', 'title_uz' => 'Internet-do‘kon',
            'summary_ru' => 'Создание магазина', 'summary_uz' => 'Do‘kon yaratish',
            'service_category_id' => $child->id, 'technology_ids' => [$technology->id], 'sort_order' => 0,
        ])->assertRedirect();
        $service = Service::where('slug', 'shop')->firstOrFail();
        $this->assertSame($child->id, $service->service_category_id);
        $this->assertTrue($service->technologies->contains($technology));
        $this->post("/studio/services/{$service->id}/publish")->assertRedirect();
        $this->post("/studio/categories/{$root->id}/publish")->assertRedirect();
        $this->post("/studio/categories/{$child->id}/publish")->assertRedirect();

        $this->get('/ru/services?category=web')->assertOk()->assertSee('Интернет-магазин')->assertSee('Веб / Магазины')->assertSee('Laravel');
        $this->get('/uz/services?category=shops')->assertOk()->assertSee('Internet-do‘kon')->assertSee('Veb / Do‘konlar');
        $this->get('/ru/services/shop')->assertOk()->assertSee('Стек технологий')->assertSee('Laravel');
        $this->assertNotNull(Service::where('slug', 'landing-page')->firstOrFail()->service_category_id);
    }

    public function test_categories_cannot_form_cycles_or_be_deleted_while_in_use(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::factory()->create(['is_owner' => true]));
        $root = ServiceCategory::create(['slug' => 'root', 'title' => ['ru' => 'Корень', 'uz' => 'Ildiz']]);
        $child = ServiceCategory::create(['slug' => 'child', 'title' => ['ru' => 'Дочерняя', 'uz' => 'Bola'], 'parent_id' => $root->id]);
        $this->put("/studio/categories/{$root->id}", [
            'slug' => 'root', 'title_ru' => 'Корень', 'title_uz' => 'Ildiz', 'parent_id' => $child->id, 'sort_order' => 0,
        ])->assertSessionHasErrors('parent_id');
        $this->assertNull($root->fresh()->parent_id);
        $this->delete("/studio/categories/{$root->id}")->assertSessionHasErrors('category');
    }

    public function test_technology_attaches_to_case_and_permissions_protect_taxonomy(): void
    {
        $this->seed(DatabaseSeeder::class);
        $technology = Technology::create(['name' => 'Vue', 'slug' => 'vue', 'kind' => 'frontend']);
        $role = Role::create(['name' => 'Редактор']);
        $role->permissions()->sync(Permission::where('resource', 'technologies')->where('action', 'view')->pluck('id'));
        $this->actingAs(User::factory()->create(['role_id' => $role->id]));
        $this->get('/studio/technologies')->assertOk();
        $this->get('/studio/categories')->assertForbidden();
        $this->post('/studio/technologies', ['name' => 'React', 'slug' => 'react', 'kind' => 'frontend', 'sort_order' => 0])->assertForbidden();

        $this->actingAs(User::factory()->create(['is_owner' => true]));
        $this->post('/studio/cases', [
            'slug' => 'showcase', 'title_ru' => 'Кейс', 'title_uz' => 'Loyiha',
            'summary_ru' => 'Описание', 'summary_uz' => 'Tavsif', 'technology_ids' => [$technology->id], 'sort_order' => 0,
        ])->assertRedirect();
        $case = CaseStudy::where('slug', 'showcase')->firstOrFail();
        $this->assertTrue($case->technologies->contains($technology));
        $this->post("/studio/cases/{$case->id}/publish")->assertRedirect();
        $this->get('/ru/cases/showcase')->assertOk()->assertSee('Vue');
        $this->delete("/studio/technologies/{$technology->id}")->assertSessionHasErrors('technology');
    }
}

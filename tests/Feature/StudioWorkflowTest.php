<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Client;
use App\Models\Lead;
use App\Models\Permission;
use App\Models\Project;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudioWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_site_has_two_languages_and_only_published_content(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->get('/ru')->assertOk()->assertSee('Разработка сайтов, телеграм ботов');
        $this->get('/uz')->assertOk()->assertSee('Telegram botlar');
        $this->get('/ru/cases')->assertOk()->assertDontSee('Ваш первый кейс');
        $this->get('/ru/cases/your-first-project')->assertNotFound();
        $owner = User::factory()->create(['is_owner' => true]);
        $case = CaseStudy::where('slug', 'your-first-project')->firstOrFail();
        $this->actingAs($owner)->post("/studio/cases/{$case->id}/publish")->assertRedirect();
        $this->get('/ru/cases')->assertSee('Ваш первый кейс');
    }

    public function test_contact_request_is_saved_and_can_be_converted_once(): void
    {
        $this->post('/ru/contact', [
            'name' => 'Иван', 'email' => 'ivan@example.test', 'message' => 'Нужен новый сайт компании',
        ])->assertRedirect('/ru/contact');
        $lead = Lead::firstOrFail();
        $owner = User::factory()->create(['is_owner' => true]);
        $this->actingAs($owner)->post("/studio/leads/{$lead->id}/convert", ['title' => 'Корпоративный сайт'])
            ->assertRedirect(route('studio.resources.index', 'projects'));
        $this->assertDatabaseHas('clients', ['name' => 'Иван', 'email' => 'ivan@example.test']);
        $this->assertDatabaseHas('projects', ['lead_id' => $lead->id, 'title' => 'Корпоративный сайт']);
        $this->post("/studio/leads/{$lead->id}/convert", ['title' => 'Повтор'])->assertStatus(409);
        $this->assertSame(1, Project::count());
    }

    public function test_contact_form_requires_a_way_to_reply_and_rejects_honeypot(): void
    {
        $this->post('/uz/contact', ['name' => 'Ali', 'message' => 'Yangi sayt kerak'])->assertSessionHasErrors();
        $this->post('/uz/contact', [
            'name' => 'Ali', 'email' => 'ali@example.test', 'message' => 'Yangi sayt kerak', 'website' => 'spam',
        ])->assertSessionHasErrors('website');
        $this->assertDatabaseCount('leads', 0);
    }

    public function test_permissions_limit_sections_and_publication(): void
    {
        $this->seed(DatabaseSeeder::class);
        $role = Role::create(['name' => 'Редактор']);
        $role->permissions()->sync(Permission::where('resource', 'services')->whereIn('action', ['view', 'create', 'update'])->pluck('id'));
        $member = User::factory()->create(['role_id' => $role->id]);
        $this->get('/studio')->assertRedirect('/login');
        $this->actingAs($member)->get('/studio/services')->assertOk();
        $this->get('/studio/clients')->assertForbidden();
        $this->post('/studio/services', [
            'slug' => 'seo', 'title_ru' => 'SEO', 'title_uz' => 'SEO', 'summary_ru' => 'Поиск',
            'summary_uz' => 'Qidiruv', 'sort_order' => 0,
        ])->assertRedirect();
        $this->post('/studio/services/'.Service::where('slug', 'seo')->firstOrFail()->id.'/publish')->assertForbidden();
        $this->assertDatabaseHas('services', ['slug' => 'seo', 'is_published' => false]);
    }

    public function test_owner_can_assign_task_and_open_all_crm_forms(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::factory()->create(['is_owner' => true]);
        $member = User::factory()->create();
        $client = Client::create(['name' => 'Компания']);
        $project = Project::create(['client_id' => $client->id, 'title' => 'Сайт']);
        $this->actingAs($owner)->post('/studio/tasks', [
            'project_id' => $project->id, 'title' => 'Макеты', 'status' => 'todo', 'priority' => 'high',
            'assignee_id' => $member->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('project_tasks', ['title' => 'Макеты', 'assignee_id' => $member->id]);
        foreach (['clients', 'projects', 'tasks', 'services', 'cases'] as $resource) {
            $this->get("/studio/{$resource}/create")->assertOk();
        }
        $this->get('/studio/team/create')->assertOk();
        $this->get('/studio/roles/create')->assertOk();
        $this->get('/studio/settings')->assertOk();
    }

    public function test_temporary_password_requires_change(): void
    {
        $member = User::factory()->create(['must_change_password' => true]);
        $this->actingAs($member)->get('/studio')->assertRedirect('/studio/password');
        $this->get('/studio/password')->assertOk();
    }

    public function test_owner_can_create_role_and_staff_member(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::factory()->create(['is_owner' => true]);
        $permission = Permission::where('resource', 'tasks')->where('action', 'view')->firstOrFail();
        $this->actingAs($owner)->post('/studio/roles', [
            'name' => 'Исполнитель', 'permissions' => [$permission->id],
        ])->assertRedirect('/studio/roles');
        $role = Role::where('name', 'Исполнитель')->firstOrFail();
        $this->post('/studio/team', [
            'name' => 'Анна', 'email' => 'anna@example.test', 'role_id' => $role->id,
            'password' => 'strong-test-password', 'password_confirmation' => 'strong-test-password',
        ])->assertRedirect('/studio/team');
        $this->assertDatabaseHas('users', ['email' => 'anna@example.test', 'role_id' => $role->id, 'must_change_password' => true]);
    }
}

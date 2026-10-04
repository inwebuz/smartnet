<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', Role::class);

        return view('studio.roles.index', ['roles' => Role::withCount('users')->orderBy('name')->get()]);
    }

    public function create()
    {
        Gate::authorize('create', Role::class);

        return view('studio.roles.form', ['role' => new Role, 'permissions' => Permission::orderBy('resource')->orderBy('action')->get()]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Role::class);
        $data = $this->validated($request);
        $role = Role::create(['name' => $data['name']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return redirect()->route('studio.roles.index')->with('message', 'Роль создана.');
    }

    public function edit(Role $role)
    {
        Gate::authorize('update', $role);

        return view('studio.roles.form', ['role' => $role->load('permissions'), 'permissions' => Permission::orderBy('resource')->orderBy('action')->get()]);
    }

    public function update(Request $request, Role $role)
    {
        Gate::authorize('update', $role);
        $data = $this->validated($request, $role->id);
        $role->update(['name' => $data['name']]);
        $role->permissions()->sync($data['permissions'] ?? []);

        return back()->with('message', 'Роль обновлена.');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('delete', $role);
        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Сначала назначьте сотрудникам другую роль.']);
        }
        $role->delete();

        return redirect()->route('studio.roles.index')->with('message', 'Роль удалена.');
    }

    private function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles')->ignore($id)],
            'permissions' => ['array'], 'permissions.*' => ['integer', 'exists:permissions,id'],
        ]);
    }
}

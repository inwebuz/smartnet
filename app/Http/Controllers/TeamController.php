<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TeamController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', User::class);

        return view('studio.team.index', ['users' => User::with('role')->orderBy('name')->get()]);
    }

    public function create()
    {
        Gate::authorize('create', User::class);

        return view('studio.team.form', ['member' => new User, 'roles' => Role::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', User::class);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'], 'password' => ['required', 'confirmed', Password::min(12)],
        ]);
        User::create($data + ['must_change_password' => true]);

        return redirect()->route('studio.team.index')->with('message', 'Сотрудник добавлен.');
    }

    public function edit(User $member)
    {
        Gate::authorize('update', $member);

        return view('studio.team.form', ['member' => $member, 'roles' => Role::orderBy('name')->get()]);
    }

    public function update(Request $request, User $member)
    {
        Gate::authorize('update', $member);
        abort_if($member->is_owner, 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', Rule::unique('users')->ignore($member->id)],
            'role_id' => ['required', 'exists:roles,id'], 'password' => ['nullable', 'confirmed', Password::min(12)],
        ]);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['must_change_password'] = true;
        }
        $member->update($data);

        return back()->with('message', 'Сотрудник обновлён.');
    }

    public function destroy(User $member)
    {
        Gate::authorize('delete', $member);
        abort_if($member->is_owner || $member->is(auth()->user()), 403);
        $member->delete();

        return redirect()->route('studio.team.index')->with('message', 'Сотрудник удалён.');
    }
}

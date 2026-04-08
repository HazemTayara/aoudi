<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laratrust\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin');
    }

    public function index()
    {
        $users = User::with('roles')->where('email', '!=',Auth::user()->email)->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::whereIn('name', ['super-admin', 'admin', 'employee'])->get();
        return view('users.create', compact('roles'));
    }

    public function store(UserStoreRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $user->addRole($request->role);
        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['super-admin', 'admin', 'employee'])->get();
        $userRole = $user->roles->first();
        return view('users.edit', compact('user', 'roles', 'userRole'));
    }

    public function update(UserUpdateRequest $request, User $user)
    {
        $data = ['name' => $request->name, 'email' => $request->email];
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        if ($request->role && $user->roles->first()->name !== $request->role) {
            $user->syncRoles([$request->role]);
        }
        return redirect()->route('users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors('You cannot delete yourself.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }
}
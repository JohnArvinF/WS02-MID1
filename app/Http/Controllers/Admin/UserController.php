<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('role', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('dir', 'desc');

        if (in_array($sort, ['name', 'email', 'role', 'created_at'])) {
            $query->orderBy($sort, $dir);
        }

        $users = $query->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,editor,viewer',
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect('/users')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'role' => 'required|in:admin,editor,viewer',
            'password' => 'nullable|min:6',
        ]);

        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect('/users')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if(auth()->id() === $user->id, 403);

        $user->delete();

        return back()->with('success', 'User deleted.');
    }
}

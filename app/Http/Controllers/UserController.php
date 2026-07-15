<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'cellphone' => ['required', 'regex:/^09[0|1|2|3][0-9]{8}$/', 'unique:users,cellphone'],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:5',
        ]);

        User::create([
            'name' => $request->name,
            'cellphone' => $request->cellphone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('user.index')->with('success', 'کاربر با موفقیت ایجاد شد');
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string',
            'cellphone' => ['required', 'regex:/^09[0|1|2|3][0-9]{8}$/', 'unique:users,cellphone,' . $user->id],
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'required|min:5',
        ]);

        $user->update([
            'name' => $request->name,
            'cellphone' => $request->cellphone,
            'email' => $request->email,
            'password' => $request->has('password') ? Hash::make($request->password) : $user->password,
        ]);

        return redirect()->route('user.index')->with('success', 'کاربر با موفقیت ویرایش شد');
    }
}

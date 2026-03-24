<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return User::select('id', 'name', 'email', 'role')->get();
    }

    public function store(Request $request)
    {
        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);
    }

    public function destroy($id)
    {
        User::destroy($id);
        return response()->json(['message' => 'deleted']);
    }
}

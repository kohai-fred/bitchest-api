<?php

// app/Http/Controllers/UserController.php

namespace App\Http\Controllers;

use App\Http\Requests\UserFormRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function getCurrentUser()
    {
        return Auth::user();
    }

    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $exists = User::where('email', $email)->exists();

        return response()->json(['exists' => $exists, 'email' => $email]);
    }

    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Permission denied'], 403);
        }
        return User::all();
    }

    public function store(UserFormRequest $request)
    {
        $user = User::create([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'presentation' => $request->input('presentation'),
        ]);

        if ($user->role === 'client') {
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
            ]);
        }

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }


    public function update(UserFormRequest $request, User $user)
    {
        $user->update($request->validated());

        return response()->json(['message' => 'Profil mis à jour avec succès']);
    }
    public function destroy(User $user)
    {
        // Supprimer les enregistrements liés dans la table transactions
        $user->transactions()->delete();
        // Supprimer les enregistrements liés dans la table wallets
        $user->wallet()->delete();

        $user->delete();
        return User::all();
    }
}

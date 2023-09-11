<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function authenticate(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (!$user)
            return response()->json([
                'message' => 'Non autorisé',
                'status' => 401
            ]);

        if (Hash::check($request->password, $user->password)) {
            $user->token = $user->createToken(time())->plainTextToken;
            return response()->json([
                'message' => 'Success',
                'user' => $user,
                'status' => 200
            ]);
        } else {
            return response()->json([
                'message' => 'Non autorisé',
                'status' => 401
            ]);
        }
    }
}

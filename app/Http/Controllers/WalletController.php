<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function getBalance()
    {
        $user = Auth::user();

        if ($user->role !== "client") {
            return response()->json(['message' => "Unauthorized"], 401);
        };

        $balance = $user->wallet->balance;
        return response()->json(['balance' => $balance]);
    }
}

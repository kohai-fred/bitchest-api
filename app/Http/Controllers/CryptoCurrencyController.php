<?php

namespace App\Http\Controllers;

use App\Models\CryptoCurrency;
use Illuminate\Http\Request;

class CryptoCurrencyController extends Controller
{
    public function index()
    {
        $currencies = CryptoCurrency::all();
        return response()->json($currencies);
    }
}

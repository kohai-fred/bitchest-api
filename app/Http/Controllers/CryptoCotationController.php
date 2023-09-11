<?php

namespace App\Http\Controllers;

use App\Models\CryptoCotation;
use App\Models\CryptoCurrency;
use Illuminate\Http\Request;

class CryptoCotationController extends Controller
{
    // public function all()
    // {
    //     $cryptosWithCotations = CryptoCotation::with('cryptocurrency')
    //         ->orderBy('timestamp', 'desc')
    //         ->get();

    //     return response()->json(['cryptosWithCotations' => $cryptosWithCotations]);
    // }

    public function latestCotation()
    {
        $cryptosWithLatestCotations = CryptoCurrency::with(['latestCotation'])
            ->get();

        return response()->json($cryptosWithLatestCotations);
    }

    public function cryptoCotationDetails($id)
    {
        $cryptoCotations = CryptoCotation::where('crypto_currency_id', $id)
            ->orderBy('timestamp')
            ->get();

        $cryptocurrency = Cryptocurrency::findOrFail($id);

        $response = [
            'cryptocurrency' => $cryptocurrency,
            'cotations' => $cryptoCotations,
        ];
        return response()->json($response);
    }
}

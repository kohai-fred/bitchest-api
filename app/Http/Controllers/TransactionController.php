<?php

namespace App\Http\Controllers;

use App\Models\CryptoCotation;
use App\Models\CryptoCurrency;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{

    public function getAllUserTransactions()
    {
        $user = Auth::user();

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Récupére toutes les transactions du client triées par crypto et ordonnées par date
        $transactions = DB::table('transactions')
            ->join('crypto_currencies', 'transactions.crypto_currency_id', '=', 'crypto_currencies.id')
            ->leftJoin('crypto_cotations', function ($join) {
                $join->on('transactions.crypto_currency_id', '=', 'crypto_cotations.crypto_currency_id')
                    ->on('transactions.created_at', '=', 'crypto_cotations.timestamp');
            })
            ->where('transactions.user_id', $user->id)
            ->orderBy('transactions.created_at', 'asc')
            ->select(
                'crypto_currencies.name as crypto_name',
                'crypto_currencies.symbol as crypto_symbol',
                'transactions.*',
                'crypto_cotations.price as crypto_price'
            )
            ->get();

        // Organise les transactions par crypto-monnaie
        $cryptoData = [];
        $cryptoTemp = [];

        foreach ($transactions as $transaction) {

            if (!isset($cryptoData[$transaction->crypto_name])) {
                $cryptoData[$transaction->crypto_name] = [];
                $cryptoTemp[$transaction->crypto_name] = [];
            }

            if ($transaction->type === 'buy') {
                $gainOrLoss = null;
                $percentageGainOrLoss = null;
                $cryptoTemp[$transaction->crypto_name][] = $transaction->price;
            } else {
                $tableau = $cryptoTemp[$transaction->crypto_name];
                $total = array_sum(array_map('floatval', $tableau)); // 328.37

                $percentageGainOrLoss = round(floatval(($transaction->price - $total) / $total * 100), 2);
                $gainOrLoss = round(floatval($transaction->price - $total), 4);
                $cryptoTemp[$transaction->crypto_name] = [];
            }

            $cryptoData[$transaction->crypto_name][] = [
                'id' => $transaction->id,
                'user_id' => $user->id,
                'crypto_currency_id' => $transaction->crypto_currency_id,
                'name' => $transaction->crypto_name,
                'symbol' => $transaction->crypto_symbol,
                'quantity' => $transaction->quantity,
                'price' => $transaction->price,
                'type' => $transaction->type,
                'created_at' => $transaction->created_at,
                'crypto_price_cotation' => $transaction->crypto_price,
                'percentage_gain_loss' => $percentageGainOrLoss,
                'amount_gain_loss' => $gainOrLoss,
            ];
        }

        return response()->json($cryptoData);
    }

    public function getRemainingQuantityOfCrypto($cryptoId)
    {
        $userId = auth()->user()->id;

        // Calcule la quantité totale achetée de cette crypto par l'utilisateur
        $totalBoughtQuantity = Transaction::where('user_id', $userId)
            ->where('crypto_currency_id', $cryptoId)
            ->where('type', 'buy')
            ->sum('quantity');

        // Calcule la quantité totale vendue de cette crypto par l'utilisateur
        $totalSoldQuantity = Transaction::where('user_id', $userId)
            ->where('crypto_currency_id', $cryptoId)
            ->where('type', 'sell')
            ->sum('quantity');

        // Calcule la quantité restante
        $remainingQuantity = round(floatval($totalBoughtQuantity - $totalSoldQuantity), 2);

        return response()->json($remainingQuantity);
    }

    public function buyCrypto(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'client') {
            $cryptoId = $request->input('crypto_id');
            $quantity = $request->input('quantity');

            // Recherche la dernière cotation pour la crypto-monnaie
            $latestCotation = CryptoCotation::where('crypto_currency_id', $cryptoId)
                ->orderBy('timestamp', 'desc')
                ->first();

            if (!$latestCotation) {
                return response()->json(['message' => 'Pas de crypto trouvé'], 400);
            }

            $totalCost = $quantity * $latestCotation->price;

            // Vérifie si le client a suffisamment de fonds
            $wallet = Wallet::where('user_id', $user->id)->first();

            if ($wallet->balance >= $totalCost) {
                // Crée une nouvelle transaction d'achat
                $transaction = new Transaction([
                    'user_id' => $user->id,
                    'crypto_currency_id' => $latestCotation->crypto_currency_id,
                    'type' => 'buy',
                    'quantity' => $quantity,
                    'price' => $totalCost,
                ]);

                $transaction->save();

                // Mets à jour le solde du portefeuille du client
                $wallet->balance -= $totalCost;
                $wallet->save();

                $ownedCrypto = $this->getUserOwnedCryptoData($request);

                return response()->json(['message' => 'Achat réussi', 'transaction' => $transaction, 'balance' => $wallet->balance, "ownedCrypto" => $ownedCrypto->original]);
            } else {
                return response()->json(['message' => 'Solde insuffisant'], 400);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function sellCrypto(Request $request)
    {
        $user = $request->user();

        $cryptoId = $request->input('crypto_id');

        $wallet = Wallet::where('user_id', $user->id)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Portefeuille introuvable pour cet utilisateur.'], 404);
        }

        // Calcule la quantité restante à vendre
        $remainingQuantity = $user->totalQuantityToSell($cryptoId);

        if ($remainingQuantity <= 0) {
            return response()->json(['message' => 'Vous ne possédez pas cette crypto-monnaie à vendre.'], 403);
        }

        // Obtien le cours de la crypto-cotation la plus récente
        $latestCotation = CryptoCotation::where('crypto_currency_id', $cryptoId)
            ->orderBy('timestamp', 'desc')
            ->first();

        if (!$latestCotation) {
            return response()->json(['message' => 'Cours de la crypto-monnaie introuvable.'], 404);
        }

        $totalAmount = round($remainingQuantity * $latestCotation->price, 4);

        $transaction = new Transaction([
            'user_id' => $user->id,
            'crypto_currency_id' => $cryptoId,
            'quantity' => $remainingQuantity,
            'price' => $totalAmount,
            'type' => 'sell',
            'created_at' => now(),
        ]);
        $transaction->save();

        // Met à jour le solde du portefeuille
        $wallet->balance += $totalAmount;
        $wallet->save();

        return response()->json(['transaction' => $transaction, 'wallet' => $wallet,]);
    }

    public function getUserOwnedCryptoData(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cryptoCurrencies = CryptoCurrency::all();

        // Initialise un tableau pour stocker les données des crypto-monnaies possédées par l'utilisateur
        $userCryptoData = [];

        foreach ($cryptoCurrencies as $cryptoCurrency) {
            $cryptoId = $cryptoCurrency->id;

            $availableQuantityToSell = $user->totalQuantityToSell($cryptoId);

            // Stocke les informations dans le tableau de données si il y a une quantité
            if ($availableQuantityToSell) {
                // Obtenez la dernière cotation de cette crypto-monnaie
                $latestCotation = $cryptoCurrency->latestCotation;

                // Calcule le gain potentiel en cas de vente avec la dernière cotation
                $potentialGain = round($availableQuantityToSell * $latestCotation->price, 4);

                $userCryptoData[] = [
                    'id' => $cryptoId,
                    'name' => $cryptoCurrency->name,
                    'symbol' => $cryptoCurrency->symbol,
                    'available_quantity_to_sell' => $availableQuantityToSell,
                    'latest_cotation' => $latestCotation->price,
                    'potential_gain' => $potentialGain,
                ];
            }
        }

        return response()->json($userCryptoData);
    }

    // TODO: faire une résumé des transactions
    public function getTransactionSummary(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'client') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Nombre total de transactions
        $totalTransactions = $user->transactions()->count();

        // Nombre d'achats
        $totalBuys = $user->transactions()->where('type', 'buy')->count();

        // Nombre de ventes
        $totalSells = $user->transactions()->where('type', 'sell')->count();

        // Crypto la plus utilisée par l'utilisateur (en termes de transactions)
        $mostUsedCrypto = $user->transactions()
            ->select('crypto_currency_id', DB::raw('COUNT(*) as count'))
            ->where('user_id', $user->id)
            ->groupBy('crypto_currency_id')
            ->orderByDesc('count')
            ->first();

        $mostUsedCryptoId = $mostUsedCrypto->crypto_currency_id;
        $mostUsedCryptoName = CryptoCurrency::find($mostUsedCryptoId)->name;

        // Montant total investi
        $totalInvested = $user->transactions()->where('type', 'buy')->sum(DB::raw('quantity * price'));

        // Montant total récupéré
        $totalRecovered = $user->transactions()->where('type', 'sell')->sum(DB::raw('quantity * price'));

        // Nombre total de crypto-monnaies dans lesquelles le client a investi
        $totalCryptoInvested = $user->transactions()->distinct('crypto_currency_id')->count('crypto_currency_id');

        // TODO: calculer le total des crypto à vendre suivant les cotations.
        $potentialRecovery = 0;

        // Parcourez les transactions de vente pour calculer le montant total déjà vendu
        $sellTransactions = $user->transactions()->where('type', 'sell')->get();
        foreach ($sellTransactions as $sellTransaction) {
            // Obtenez la cotation de la crypto à vendre au moment de la transaction
            $cryptoPrice = $sellTransaction->crypto_price;

            // Calculez le montant de la vente en cours de cette transaction
            $sellAmount = $sellTransaction->quantity * $cryptoPrice;

            // Ajoutez le montant de cette vente au total des ventes déjà effectuées
            $potentialRecovery += $sellAmount;
        }

        // Calculez le montant total investi par l'utilisateur
        $totalInvested = $user->transactions()
            ->where('type', 'buy')
            ->sum(DB::raw('quantity * price'));

        // Soustrayez le montant total des ventes déjà effectuées du montant total investi
        $potentialRecovery = max(0, $totalInvested - $potentialRecovery);

        $summary = [
            'total_transactions' => $totalTransactions,
            'total_buys' => $totalBuys,
            'total_sells' => $totalSells,
            'most_used_crypto' => $mostUsedCryptoName,
            'total_invested' => $totalInvested,
            'total_recovered' => $totalRecovered,
            'total_crypto_invested' => $totalCryptoInvested,
            'potential_recovery' => $potentialRecovery,
        ];

        return response()->json($summary);
    }
}

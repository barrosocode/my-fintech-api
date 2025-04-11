<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function index(Request $request)
    {
        $balance = $request->user()->balance;

        $transactions = $balance?->transactions()
            ->orderBy('date', 'desc')
            ->latest()
            ->get();

        return TransactionResource::collection($transactions);

        // $transactions = $request->user()->orderBy('date', 'desc')->latest()->get();

        // return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request)
    {
        $user = $request->user();
        $balance = $user->balance;
        $transaction = $request->balance()->transactions()->create($request->validated());

        if ($transaction->type === 'entrada') {
            $balance->amount += $transaction->value;
        } else {
            $balance->amount -= $transaction->value;
        }

        $balance->save();

        return new TransactionResource($transaction);
    }
}

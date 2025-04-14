<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\Request;

class TransactionController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $balance = $request->user()->balance;

        $query = $balance?->transactions()
            ->orderBy('date', 'desc');

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $transactions = $query->latest()->get();

        return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request)
    {
        try {
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
        } catch (\Illuminate\Database\QueryException $e) {
            // Erro ao interagir com o banco de dados
            return response()->json([
                'message' => 'Erro ao tentar salvar a transação no banco de dados.',
                'error' => $e->getMessage()
            ], 500);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Erro de validação
            return response()->json([
                'message' => 'Erro de validação nos dados enviados.',
                'errors' => $e->errors() // Exibe os erros de validação
            ], 422);
        } catch (\Throwable $th) {
            // Qualquer outro erro inesperado
            return response()->json([
                'message' => 'Erro ao tentar cadastrar a transação.',
                'error' => $th->getMessage() // Pode ajudar a entender o erro
            ], 500);
        }
    }
}

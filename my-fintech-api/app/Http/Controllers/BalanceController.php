<?php

namespace App\Http\Controllers;

use App\Http\Resources\BalanceResource;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function index(Request $request)
    {
        $balances = $request->user()->balance;

        return new BalanceResource($balances);
    }
}

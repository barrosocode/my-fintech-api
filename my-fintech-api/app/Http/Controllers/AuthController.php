<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{



    public function login(Request $request)
    {
        // Validação manual
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Erro de validação nos dados enviados.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!auth('web')->attempt($credentials)) {
            return response()->json(['message' => 'Email ou senha incorretos.'], 401);
        }

        $user = Auth::user();

        // Cria o token
        $tokenResult = $user->createToken('token-api');

        if ($tokenResult instanceof \Laravel\Sanctum\NewAccessToken) {
            $token = $tokenResult->plainTextToken;

            $expiresAt = now()->addMinutes(40);

            $tokenModel = $tokenResult->accessToken;
            $tokenModel->expires_at = $expiresAt;
            $tokenModel->save();
        } else {
            // Fallback: só retorna o token (sem expiração registrada)
            $token = $tokenResult;
            $expiresAt = null;
        }

        return response()->json([
            'token' => $token,
            'user' => $user,
            'expires_at' => $expiresAt?->toDateTimeString(),
        ]);
    }


    public function logout(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Usuário já está deslogado.'], 401);
        }

        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout realizado com sucesso!']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro ao realizar logout.'], 500);
        }
    }

    public function me(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        return response()->json($request->user());
    }

    public function register(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        $token = $user->createToken('token-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }
}

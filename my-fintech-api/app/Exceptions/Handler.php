<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Uma lista das exceções que não devem ser reportadas.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Uma lista das entradas que nunca devem ser exibidas em exceções de validação.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Reportar ou registrar uma exceção.
     */
    public function register(): void
    {
        //
    }

    /**
     * Personalizar a resposta para exceções de autenticação.
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            // Verifica se é uma rota da API
            if ($request->is('api/*')) {
                return response()->json([
                    'message' => 'Não autenticado.',
                ], 401);
            }
        }

        return parent::render($request, $exception);
    }
}

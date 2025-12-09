<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Tratamento específico para erro 419 (Token CSRF expirado)
        if ($e instanceof TokenMismatchException) {
            // Se for uma rota de login do tenant, redirecionar de volta com mensagem
            if ($request->routeIs('tenant.login.submit') || $request->is('customer/*/login')) {
                $slug = $request->route('slug');
                return redirect()
                    ->route('tenant.login', ['slug' => $slug])
                    ->with('error', 'Sua sessão expirou. Por favor, tente fazer login novamente.')
                    ->withInput($request->except(['password', '_token']));
            }
        }

        return parent::render($request, $e);
    }
}

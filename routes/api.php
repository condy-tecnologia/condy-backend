<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

// Rotas de autenticação públicas
Route::prefix('auth')->group(function () {
    // Registro e login
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Verificações para formulários
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
    Route::post('/check-cpf-cnpj', [AuthController::class, 'checkCpfCnpj']);
});

// Rotas protegidas por autenticação
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        // Dados do usuário autenticado
        Route::get('/me', [AuthController::class, 'me']);

        // Logout
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);

        // Atualizar senha
        Route::put('/update-password', [AuthController::class, 'updatePassword']);
    });
});
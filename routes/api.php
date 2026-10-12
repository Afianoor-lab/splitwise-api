<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\SettlementController;

// Public Auth Endpoints
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated Endpoints (Using Custom Mongo Middleware)
Route::middleware(\App\Http\Middleware\MongoAuthMiddleware::class)->group(function () {
    
    // User Profile & Logout
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Groups Management
    Route::get('/groups', [GroupController::class, 'index']);
    Route::post('/groups', [GroupController::class, 'store']);
    Route::get('/groups/{group}', [GroupController::class, 'show']);
    Route::post('/groups/{group}/members', [GroupController::class, 'addMember']);
    Route::delete('/groups/{group}/members/{user}', [GroupController::class, 'removeMember']);

    // Expenses
    Route::post('/groups/{group}/expenses', [ExpenseController::class, 'store']);
    Route::get('/groups/{group}/expenses', [ExpenseController::class, 'index']);
    Route::get('/expenses/{expense}', [ExpenseController::class, 'show']);
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Balances, Debt Simplification & Settlements
    Route::get('/groups/{group}/balances', [GroupController::class, 'getBalances']);
    Route::get('/groups/{group}/simplified-debts', [GroupController::class, 'getSimplifiedDebts']);
    Route::post('/groups/{group}/settlements', [SettlementController::class, 'store']);
    Route::get('/groups/{group}/settlements', [SettlementController::class, 'index']); // Added GET Route
});
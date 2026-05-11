<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Models\ExchangeRate;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AdminController;
use App\Models\Transaction;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // 1. Taxas para a calculadora
    $rates = App\Models\ExchangeRate::where('is_active', true)->get();
    
    // 2. Transações do utilizador logado (as mais recentes primeiro)
    $transactions = Transaction::where('user_id', Auth::id())
                                ->orderBy('created_at', 'desc')
                                ->take(5) // Mostra apenas as 5 últimas no dashboard
                                ->get();

    return view('dashboard', compact('rates', 'transactions'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/transaction', [TransactionController::class, 'store'])->middleware(['auth'])->name('transaction.store');
Route::get('/transaction/{reference_id}', [TransactionController::class, 'show'])->middleware(['auth'])->name('transaction.show');
// Confirma que esta linha de baixo existe e está como POST:
Route::post('/transaction/{reference_id}/upload', [TransactionController::class, 'uploadReceipt'])->middleware(['auth'])->name('transaction.upload');

// Rotas exclusivas para o Administrador
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/transaction/{id}', [AdminController::class, 'show'])->name('transaction.show');
    Route::post('/transaction/{id}/approve', [AdminController::class, 'approve'])->name('transaction.approve');
});

require __DIR__.'/auth.php';

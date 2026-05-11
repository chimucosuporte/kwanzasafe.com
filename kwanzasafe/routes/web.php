<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuditController;
use App\Models\Transaction;
use App\Models\ExchangeRate;
use App\Models\ChatMessage;
use App\Http\Controllers\OtpController;

/*
|--------------------------------------------------------------------------
| KwanzaSafe — web.php (ATUALIZADO Sprint 3B — Admin Vivo)
|--------------------------------------------------------------------------
*/

// PÚBLICAS
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/termos',      fn() => view('legal.terms'))  ->name('terms');
Route::get('/privacidade', fn() => view('legal.privacy'))->name('privacy');

// DASHBOARD CLIENTE
Route::get('/dashboard', function () {
    $user  = Auth::user();
    $rates = ExchangeRate::where('is_active', true)->get();
    $transactions = Transaction::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')->get();
    $beneficiaries = $user->beneficiaries()->orderBy('created_at', 'desc')->get();

    $unreadMessages = ChatMessage::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
                        ->where('sender_id', '!=', $user->id)
                        ->where('is_read', false)
                        ->count();

    return view('dashboard', compact('rates', 'transactions', 'beneficiaries', 'unreadMessages'));
})->middleware(['auth', 'verified'])->name('dashboard');

// AÇÕES CLIENTE
Route::middleware('auth')->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/verify/data',     [VerificationController::class, 'updatePersonalData'])->name('verify.data');
    Route::post('/verify/phone',    [VerificationController::class, 'updatePhone'])       ->name('verify.phone');
    Route::post('/verify/document', [VerificationController::class, 'uploadDocument'])    ->name('verify.document');
    Route::post('/verify/photo',    [VerificationController::class, 'uploadPhoto'])       ->name('verify.photo');

    Route::post('/transaction',                       [TransactionController::class, 'store'])        ->name('transaction.store');
    Route::get('/transaction/{reference_id}',         [TransactionController::class, 'show'])         ->name('transaction.show');
    Route::post('/transaction/{reference_id}/upload', [TransactionController::class, 'uploadReceipt'])->name('transaction.upload');

    Route::post('/transaction/{reference_id}/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/transaction/{reference_id}/read', [ChatController::class, 'markAsRead']) ->name('chat.read');

    Route::post('/beneficiary',        [BeneficiaryController::class, 'store'])  ->name('beneficiary.store');
    Route::delete('/beneficiary/{id}', [BeneficiaryController::class, 'destroy'])->name('beneficiary.destroy');

    Route::get('/verify/email',         [OtpController::class, 'showEmailVerification'])->name('otp.email.verify');
    Route::post('/verify/email/send',   [OtpController::class, 'sendEmailOtp'])         ->name('otp.email.send');
    Route::post('/verify/email/submit', [OtpController::class, 'verifyEmailOtp'])       ->name('otp.email.verify.submit');
});

// ============ ADMIN ============
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Painel principal + AJAX stats
    Route::get('/dashboard', [AdminController::class, 'index'])    ->name('dashboard');
    Route::get('/stats.json', [AdminController::class, 'statsJson'])->name('stats.json');

    // Transações (vista filtrada + show + ações)
    Route::get('/transactions',              [AdminController::class, 'transactionsIndex'])->name('transactions.index');
    Route::get('/transaction/{id}',          [AdminController::class, 'show'])             ->name('transaction.show');
    Route::post('/transaction/{id}/approve', [AdminController::class, 'approve'])          ->name('transaction.approve');
    Route::post('/transaction/{id}/chat',    [AdminController::class, 'sendChatMessage'])  ->name('chat.send');

    // Utilizadores
    Route::get('/users', [AdminController::class, 'usersIndex'])->name('users.index');

    // Mensagens
    Route::get('/messages/unread', [AdminController::class, 'unreadMessages'])->name('messages.unread');

    // Taxas
    Route::get('/rates',           [AdminController::class, 'ratesIndex']) ->name('rates.index');
    Route::get('/rates/{id}/edit', [AdminController::class, 'ratesEdit'])  ->name('rates.edit');
    Route::put('/rates/{id}',      [AdminController::class, 'ratesUpdate'])->name('rates.update');

    // KYC
    Route::get('/kyc',                   [AdminController::class, 'kycIndex'])  ->name('kyc.index');
    Route::get('/kyc/{userId}',          [AdminController::class, 'kycShow'])   ->name('kyc.show');
    Route::post('/kyc/{userId}/approve', [AdminController::class, 'kycApprove'])->name('kyc.approve');
    Route::post('/kyc/{userId}/reject',  [AdminController::class, 'kycReject']) ->name('kyc.reject');

    // Audit Trail
    Route::get('/audit',               [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/user/{userId}', [AuditController::class, 'user']) ->name('audit.user');
    Route::get('/audit/{id}',          [AuditController::class, 'show']) ->name('audit.show');
});

require __DIR__.'/auth.php';

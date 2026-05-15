<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\TransactionAdminController;
use App\Http\Controllers\Admin\KycAdminController;
use App\Http\Controllers\Admin\RateAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\MessageAdminController;
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
| KwanzaSafe — web.php
|--------------------------------------------------------------------------
*/

// PÚBLICAS
Route::get('/', fn() => view('welcome'))->name('home');
Route::get('/termos',      fn() => view('legal.terms'))  ->name('terms');
Route::get('/privacidade', fn() => view('legal.privacy'))->name('privacy');
Route::get('/sitemap.xml', function () {
    $lastmod = \App\Models\ExchangeRate::orderBy('updated_at', 'desc')->value('updated_at')
               ?? now();
    return response()
        ->view('sitemap', ['lastmod' => $lastmod->format('Y-m-d')])
        ->header('Content-Type', 'application/xml; charset=utf-8');
})->name('sitemap');

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

    Route::post('/transaction', [TransactionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('transaction.store');
    Route::get('/transaction/{reference_id}',         [TransactionController::class, 'show'])         ->name('transaction.show');
    Route::post('/transaction/{reference_id}/upload',  [TransactionController::class, 'uploadReceipt']) ->name('transaction.upload');
    Route::post('/transaction/{reference_id}/confirm', [TransactionController::class, 'confirmReceipt'])->name('transaction.confirm');

    Route::post('/transaction/{reference_id}/chat', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/transaction/{reference_id}/read', [ChatController::class, 'markAsRead']) ->name('chat.read');

    Route::post('/beneficiary',        [BeneficiaryController::class, 'store'])  ->name('beneficiary.store');
    Route::delete('/beneficiary/{id}', [BeneficiaryController::class, 'destroy'])->name('beneficiary.destroy');

    Route::get('/verify/email',         [OtpController::class, 'showEmailVerification'])->name('otp.email.verify');
    Route::post('/verify/email/send',   [OtpController::class, 'sendEmailOtp'])         ->name('otp.email.send');
    Route::post('/verify/email/submit', [OtpController::class, 'verifyEmailOtp'])       ->name('otp.email.verify.submit');
});

// ============ ADMIN ============
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard + AJAX stats
    Route::get('/dashboard',  [AdminController::class, 'index'])    ->name('dashboard');
    Route::get('/stats.json', [AdminController::class, 'statsJson'])->name('stats.json');

    // Transações
    Route::get('/transactions',              [TransactionAdminController::class, 'index'])          ->name('transactions.index');
    Route::get('/transaction/{id}',                   [TransactionAdminController::class, 'show'])              ->name('transaction.show');
    Route::post('/transaction/{id}/request-payment',  [TransactionAdminController::class, 'requestPayment'])   ->name('transaction.request_payment');
    Route::post('/transaction/{id}/payment-received', [TransactionAdminController::class, 'markPaymentReceived'])->name('transaction.payment_received');
    Route::post('/transaction/{id}/aoa-sent',         [TransactionAdminController::class, 'markAoaSent'])      ->name('transaction.aoa_sent');
    Route::post('/transaction/{id}/approve',          [TransactionAdminController::class, 'approve'])          ->name('transaction.approve');
    Route::post('/transaction/{id}/chat',             [TransactionAdminController::class, 'sendChatMessage'])  ->name('chat.send');

    // Utilizadores
    Route::get('/users', [UserAdminController::class, 'index'])->name('users.index');

    // Mensagens
    Route::get('/messages/unread', [MessageAdminController::class, 'unread'])->name('messages.unread');

    // Taxas
    Route::get('/rates',           [RateAdminController::class, 'index']) ->name('rates.index');
    Route::get('/rates/{id}/edit', [RateAdminController::class, 'edit'])  ->name('rates.edit');
    Route::put('/rates/{id}',      [RateAdminController::class, 'update'])->name('rates.update');

    // KYC
    Route::get('/kyc',                   [KycAdminController::class, 'index'])  ->name('kyc.index');
    Route::get('/kyc/{userId}',          [KycAdminController::class, 'show'])   ->name('kyc.show');
    Route::post('/kyc/{userId}/approve', [KycAdminController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{userId}/reject',  [KycAdminController::class, 'reject']) ->name('kyc.reject');

    // Audit Trail
    Route::get('/audit',               [AuditController::class, 'index'])->name('audit.index');
    Route::get('/audit/user/{userId}', [AuditController::class, 'user']) ->name('audit.user');
    Route::get('/audit/{id}',          [AuditController::class, 'show']) ->name('audit.show');
});

require __DIR__.'/auth.php';

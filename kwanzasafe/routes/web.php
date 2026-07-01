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
use App\Http\Controllers\Admin\StaffAdminController;
use App\Http\Controllers\Admin\StaffChatController;
use App\Http\Controllers\Admin\PaymentAccountAdminController;
use App\Http\Controllers\Admin\MessageAdminController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\BeneficiaryController;
use App\Http\Controllers\PaymentWalletController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RecourseController;
use App\Http\Controllers\Admin\RecourseAdminController;
use App\Http\Controllers\AuditController;
use App\Models\Transaction;
use App\Models\ExchangeRate;
use App\Models\ChatMessage;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| KwanzaSafe — web.php
|--------------------------------------------------------------------------
*/

// PÚBLICAS
Route::get('/', function () {
    $rates = ExchangeRate::activeCached();
    return view('welcome', compact('rates'));
})->name('home');
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
    $rates = ExchangeRate::activeCached();
    $transactions = Transaction::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')->get();
    $beneficiaries = $user->beneficiaries()->orderBy('created_at', 'desc')->get();
    $wallets = \App\Models\PaymentWallet::where('user_id', $user->id)->orderByDesc('is_default')->orderBy('created_at')->get();

    $unreadMessages = ChatMessage::whereHas('transaction', fn($q) => $q->where('user_id', $user->id))
                        ->where('sender_id', '!=', $user->id)
                        ->where('is_read', false)
                        ->count();

    $unreadNotifications = \App\Models\UserNotification::where('user_id', $user->id)
                        ->where('is_read', false)
                        ->count();

    return view('dashboard', compact('rates', 'transactions', 'beneficiaries', 'wallets', 'unreadMessages', 'unreadNotifications'));
})->middleware(['auth', 'verified'])->name('dashboard');

// AÇÕES CLIENTE
Route::middleware('auth')->group(function () {
    // Ficheiros privados (KYC, comprovativos, anexos) — servidos com autorização
    Route::get('/file/{path}', [FileController::class, 'show'])->where('path', '.*')->name('file.show');

    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Alteração de email com dupla confirmação (código no email atual + no novo)
    Route::post('/profile/email/request', [ProfileController::class, 'requestEmailChange'])->middleware('throttle:6,1')->name('profile.email.request');
    Route::post('/profile/email/confirm', [ProfileController::class, 'confirmEmailChange'])->middleware('throttle:8,1')->name('profile.email.confirm');

    Route::post('/verify/data',     [VerificationController::class, 'updatePersonalData'])->name('verify.data');
    Route::post('/verify/phone',        [VerificationController::class, 'updatePhone'])  ->middleware('throttle:6,1') ->name('verify.phone');
    Route::post('/verify/phone/submit', [VerificationController::class, 'verifyPhone'])   ->middleware('throttle:8,1') ->name('verify.phone.submit');
    Route::post('/verify/document', [VerificationController::class, 'uploadDocument'])    ->middleware('throttle:20,1') ->name('verify.document');
    Route::post('/verify/photo',    [VerificationController::class, 'uploadPhoto'])       ->middleware('throttle:20,1') ->name('verify.photo');

    Route::post('/transaction', [TransactionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('transaction.store');
    Route::get('/transaction/{reference_id}',         [TransactionController::class, 'show'])         ->name('transaction.show');
    Route::post('/transaction/{reference_id}/upload',  [TransactionController::class, 'uploadReceipt']) ->middleware('throttle:20,1') ->name('transaction.upload');
    Route::post('/transaction/{reference_id}/confirm', [TransactionController::class, 'confirmReceipt'])->name('transaction.confirm');
    Route::post('/transaction/{reference_id}/cancel',  [TransactionController::class, 'cancel'])        ->name('transaction.cancel');
    Route::get('/transaction/{reference_id}/receipt', [TransactionController::class, 'receipt'])       ->name('transaction.receipt');

    Route::post('/transaction/{reference_id}/recourse',       [RecourseController::class, 'open']) ->middleware('throttle:5,1') ->name('recourse.open');
    Route::post('/transaction/{reference_id}/recourse/reply', [RecourseController::class, 'reply'])->middleware('throttle:20,1')->name('recourse.reply');

    Route::post('/transaction/{reference_id}/chat', [ChatController::class, 'sendMessage'])->middleware('throttle:30,1')->name('chat.send');
    Route::post('/transaction/{reference_id}/read', [ChatController::class, 'markAsRead']) ->name('chat.read');
    Route::get('/transaction/{reference_id}/poll',  [ChatController::class, 'poll'])       ->middleware('throttle:90,1')->name('chat.poll');

    Route::post('/beneficiary',        [BeneficiaryController::class, 'store'])  ->name('beneficiary.store');
    Route::delete('/beneficiary/{id}', [BeneficiaryController::class, 'destroy'])->name('beneficiary.destroy');

    Route::post('/wallet',        [PaymentWalletController::class, 'store'])  ->name('wallet.store');
    Route::delete('/wallet/{id}', [PaymentWalletController::class, 'destroy'])->name('wallet.destroy');

    // Definições (hub central)
    Route::get('/settings', fn() => view('settings'))->name('settings');

    // Central de notificações
    Route::get('/notifications',              [NotificationController::class, 'index'])      ->name('notifications.index');
    Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead'])->name('notifications.read_all');
    Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead'])   ->name('notifications.read');

    Route::get('/verify/email',         [OtpController::class, 'showEmailVerification'])->name('otp.email.verify');
    Route::post('/verify/email/send',   [OtpController::class, 'sendEmailOtp'])         ->middleware('throttle:5,1') ->name('otp.email.send');
    Route::post('/verify/email/submit', [OtpController::class, 'verifyEmailOtp'])       ->middleware('throttle:8,1') ->name('otp.email.verify.submit');
});

// ============ ADMIN ============
Route::middleware(['auth', 'is_admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard + AJAX stats
    Route::get('/dashboard',  [AdminController::class, 'index'])    ->name('dashboard');
    Route::get('/stats.json', [AdminController::class, 'statsJson'])->name('stats.json');

    // Transações
    Route::get('/transactions',              [TransactionAdminController::class, 'index'])          ->name('transactions.index');
    Route::get('/transactions/export',       [TransactionAdminController::class, 'export'])->middleware('throttle:10,1')->name('transactions.export');
    Route::get('/transaction/{id}',                   [TransactionAdminController::class, 'show'])              ->name('transaction.show');
    Route::post('/transaction/{id}/request-payment',  [TransactionAdminController::class, 'requestPayment'])   ->name('transaction.request_payment');
    Route::post('/transaction/{id}/payment-received', [TransactionAdminController::class, 'markPaymentReceived'])->name('transaction.payment_received');
    Route::post('/transaction/{id}/aoa-sent',         [TransactionAdminController::class, 'markAoaSent'])      ->name('transaction.aoa_sent');
    Route::post('/transaction/{id}/approve',          [TransactionAdminController::class, 'approve'])          ->name('transaction.approve');
    Route::post('/transaction/{id}/cancel',           [TransactionAdminController::class, 'cancel'])           ->name('transaction.cancel');
    Route::get('/transaction/{id}/receipt',           [TransactionAdminController::class, 'receipt'])          ->name('transaction.receipt');
    Route::post('/transaction/{id}/assign',           [TransactionAdminController::class, 'assign'])           ->name('transaction.assign');
    Route::post('/transaction/{id}/chat',             [TransactionAdminController::class, 'sendChatMessage'])  ->middleware('throttle:60,1')->name('chat.send');
    Route::get('/transaction/{id}/poll',              [TransactionAdminController::class, 'poll'])              ->middleware('throttle:90,1')->name('transaction.poll');

    // ===== Exclusivo do Super-Admin =====
    Route::middleware('is_super_admin')->group(function () {
        Route::get('/staff',                    [StaffAdminController::class, 'index'])       ->name('staff.index');
        Route::post('/staff',                   [StaffAdminController::class, 'store'])       ->name('staff.store');
        Route::post('/staff/{id}/toggle-active', [StaffAdminController::class, 'toggleActive'])->name('staff.toggle_active');
        Route::delete('/staff/{id}',            [StaffAdminController::class, 'destroy'])     ->name('staff.destroy');

        Route::post('/transaction/{id}/reassign', [TransactionAdminController::class, 'reassign'])->name('transaction.reassign');

        // Contas de recepção (dados de pagamento por moeda)
        Route::get('/payment-accounts',         [PaymentAccountAdminController::class, 'index'])  ->name('payment_accounts.index');
        Route::post('/payment-accounts',        [PaymentAccountAdminController::class, 'store'])  ->name('payment_accounts.store');
        Route::put('/payment-accounts/{id}',    [PaymentAccountAdminController::class, 'update']) ->name('payment_accounts.update');
        Route::delete('/payment-accounts/{id}', [PaymentAccountAdminController::class, 'destroy'])->name('payment_accounts.destroy');

        // Arbitragem de recursos
        Route::get('/recourses',              [RecourseAdminController::class, 'index'])  ->name('recourses.index');
        Route::post('/recourses/{id}/reply',  [RecourseAdminController::class, 'reply'])  ->name('recourses.reply');
        Route::post('/recourses/{id}/resolve', [RecourseAdminController::class, 'resolve'])->name('recourses.resolve');
        Route::post('/recourses/{id}/reject', [RecourseAdminController::class, 'reject']) ->name('recourses.reject');
    });

    // Utilizadores
    Route::get('/users',               [UserAdminController::class, 'index'])      ->name('users.index');
    Route::get('/users/{id}',          [UserAdminController::class, 'show'])       ->name('users.show');
    Route::post('/users/{id}/toggle-admin', [UserAdminController::class, 'toggleAdmin'])->name('users.toggle_admin');

    // Mensagens
    Route::get('/messages/unread', [MessageAdminController::class, 'unread'])->name('messages.unread');

    // Canal de staff geral (suporte ↔ super-admin)
    Route::get('/staff-chat',                 [StaffChatController::class, 'index'])->name('staff_chat.index');
    Route::post('/staff-chat/{userId}',       [StaffChatController::class, 'send']) ->middleware('throttle:30,1')->name('staff_chat.send');
    Route::get('/staff-chat/{userId}/poll',   [StaffChatController::class, 'poll']) ->middleware('throttle:90,1')->name('staff_chat.poll');

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

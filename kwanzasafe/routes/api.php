<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BeneficiaryController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PaymentWalletController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PushController;
use App\Http\Controllers\Api\RateController;
use App\Http\Controllers\Api\RecourseController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TwoFactorController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Api\Admin\KycController as AdminKycController;
use App\Http\Controllers\Api\Admin\RateController as AdminRateController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\Admin\PaymentAccountController as AdminPaymentAccountController;
use App\Http\Controllers\Api\Admin\AuditController as AdminAuditController;
use App\Http\Controllers\Api\Admin\StaffController as AdminStaffController;
use App\Http\Controllers\Api\Admin\RecourseController as AdminRecourseController;
use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rota legacy do Sanctum (mantida intacta).
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
| API mobile (KwanzaSafe app) — versionada em /api/v1.
| Autenticação por tokens Sanctum (Bearer). Aditivo ao backend web.
*/
Route::prefix('v1')->group(function () {
    // Públicas (com throttle anti-força-bruta)
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

    // Recuperação de palavra-passe por OTP (público)
    Route::post('/password/forgot', [PasswordResetController::class, 'forgot'])->middleware('throttle:6,1');
    Route::post('/password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

    // Protegidas por token
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Verificação de email por OTP
        Route::post('/email/verify/send', [EmailVerificationController::class, 'send'])->middleware('throttle:6,1');
        Route::post('/email/verify', [EmailVerificationController::class, 'verify'])->middleware('throttle:10,1');

        // KYC (verificação de identidade — 4 passos)
        Route::post('/kyc/personal', [VerificationController::class, 'personalData']);
        Route::post('/kyc/phone/send', [VerificationController::class, 'sendPhoneOtp'])->middleware('throttle:6,1');
        Route::post('/kyc/phone/verify', [VerificationController::class, 'verifyPhone'])->middleware('throttle:10,1');
        Route::post('/kyc/document', [VerificationController::class, 'uploadDocument'])->middleware('throttle:10,1');
        Route::post('/kyc/photo', [VerificationController::class, 'uploadPhoto'])->middleware('throttle:10,1');

        // Perfil
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/email/request', [ProfileController::class, 'requestEmailChange'])->middleware('throttle:6,1');
        Route::post('/profile/email/confirm', [ProfileController::class, 'confirmEmailChange'])->middleware('throttle:10,1');
        Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->middleware('throttle:20,1');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::delete('/profile', [ProfileController::class, 'destroy']);

        // Feed de notificações (auditoria de IP, transações, recursos)
        Route::get('/notifications', [NotificationController::class, 'index'])->middleware('throttle:60,1');
        Route::get('/notifications/unread', [NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

        // Notificações push (token Expo)
        Route::post('/push/token', [PushController::class, 'store']);
        Route::post('/push/test', [PushController::class, 'test'])->middleware('throttle:6,1');
        Route::delete('/push/token', [PushController::class, 'destroy']);

        // Autenticação em 2 passos (TOTP / Google Authenticator)
        Route::get('/2fa', [TwoFactorController::class, 'status']);
        Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
        Route::post('/2fa/confirm', [TwoFactorController::class, 'confirm'])->middleware('throttle:10,1');
        Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);

        // Beneficiários (Cofre IBAN)
        Route::get('/beneficiaries', [BeneficiaryController::class, 'index']);
        Route::post('/beneficiaries', [BeneficiaryController::class, 'store'])->middleware('throttle:20,1');
        Route::delete('/beneficiaries/{id}', [BeneficiaryController::class, 'destroy']);

        // Carteiras de recepção (Bybit / Binance / RedotPay)
        Route::get('/wallets', [PaymentWalletController::class, 'index']);
        Route::post('/wallets', [PaymentWalletController::class, 'store'])->middleware('throttle:20,1');
        Route::post('/wallets/{id}/default', [PaymentWalletController::class, 'setDefault']);
        Route::delete('/wallets/{id}', [PaymentWalletController::class, 'destroy']);

        // Taxas / calculadora
        Route::get('/rates', [RateController::class, 'index']);

        // Transações
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store'])->middleware('throttle:5,1');
        Route::get('/transactions/{reference_id}', [TransactionController::class, 'show']);
        Route::post('/transactions/{reference_id}/receipt', [TransactionController::class, 'uploadReceipt'])->middleware('throttle:20,1');
        Route::post('/transactions/{reference_id}/confirm', [TransactionController::class, 'confirm']);
        Route::post('/transactions/{reference_id}/cancel', [TransactionController::class, 'cancel']);

        // Recursos (appeal) — abrir / ver / cancelar
        Route::get('/transactions/{reference_id}/recourse', [RecourseController::class, 'show']);
        Route::post('/transactions/{reference_id}/recourse', [RecourseController::class, 'open'])->middleware('throttle:6,1');
        Route::post('/transactions/{reference_id}/recourse/cancel', [RecourseController::class, 'cancel']);

        // Chat da sala de transação (listar/polling, enviar, marcar lidas)
        Route::get('/transactions/{reference_id}/messages', [ChatController::class, 'index'])->middleware('throttle:90,1');
        Route::post('/transactions/{reference_id}/messages', [ChatController::class, 'store'])->middleware('throttle:30,1');
        Route::post('/transactions/{reference_id}/read', [ChatController::class, 'markAsRead']);

        // Ficheiros privados (anexos/comprovativos) — autorização por token.
        Route::get('/file/{path}', [FileController::class, 'show'])->where('path', '.*');

        /*
        | ADMIN (app de administração) — requer staff (is_admin).
        | Espelha o painel admin web; reutiliza TransactionFlow/KycBot/AuditLogger.
        */
        Route::middleware('api_admin')->prefix('admin')->group(function () {
            Route::get('/stats', [AdminDashboardController::class, 'stats']);

            Route::get('/transactions', [AdminTransactionController::class, 'index']);
            Route::get('/transactions/{id}', [AdminTransactionController::class, 'show']);
            Route::post('/transactions/{id}/request-payment', [AdminTransactionController::class, 'requestPayment']);
            Route::post('/transactions/{id}/payment-received', [AdminTransactionController::class, 'paymentReceived']);
            Route::post('/transactions/{id}/aoa-sent', [AdminTransactionController::class, 'aoaSent']);
            Route::post('/transactions/{id}/approve', [AdminTransactionController::class, 'approve']);
            Route::post('/transactions/{id}/cancel', [AdminTransactionController::class, 'cancel']);
            Route::post('/transactions/{id}/assign', [AdminTransactionController::class, 'assign']);
            Route::get('/transactions/{id}/messages', [AdminTransactionController::class, 'messages'])->middleware('throttle:90,1');
            Route::post('/transactions/{id}/messages', [AdminTransactionController::class, 'sendMessage'])->middleware('throttle:60,1');

            Route::get('/kyc', [AdminKycController::class, 'index']);
            Route::get('/kyc/{userId}', [AdminKycController::class, 'show']);
            Route::post('/kyc/{userId}/approve', [AdminKycController::class, 'approve']);
            Route::post('/kyc/{userId}/reject', [AdminKycController::class, 'reject']);

            // Taxas
            Route::get('/rates', [AdminRateController::class, 'index']);
            Route::put('/rates/{id}', [AdminRateController::class, 'update']);

            // Utilizadores
            Route::get('/users', [AdminUserController::class, 'index']);
            Route::get('/users/{id}', [AdminUserController::class, 'show']);
            Route::post('/users/{id}/toggle-admin', [AdminUserController::class, 'toggleAdmin']);

            // Auditoria (leitura)
            Route::get('/audit', [AdminAuditController::class, 'index']);

            // ===== Exclusivo do super-admin =====
            Route::middleware('api_super_admin')->group(function () {
                Route::get('/payment-accounts', [AdminPaymentAccountController::class, 'index']);
                Route::post('/payment-accounts', [AdminPaymentAccountController::class, 'store']);
                Route::put('/payment-accounts/{id}', [AdminPaymentAccountController::class, 'update']);
                Route::delete('/payment-accounts/{id}', [AdminPaymentAccountController::class, 'destroy']);

                // Funcionários (staff)
                Route::get('/staff', [AdminStaffController::class, 'index']);
                Route::post('/staff', [AdminStaffController::class, 'store']);
                Route::post('/staff/{id}/toggle-active', [AdminStaffController::class, 'toggleActive']);
                Route::delete('/staff/{id}', [AdminStaffController::class, 'destroy']);

                // Recursos (arbitragem)
                Route::get('/recourses', [AdminRecourseController::class, 'index']);
                Route::get('/recourses/{id}', [AdminRecourseController::class, 'show']);
                Route::post('/recourses/{id}/reply', [AdminRecourseController::class, 'reply'])->middleware('throttle:30,1');
                Route::post('/recourses/{id}/resolve', [AdminRecourseController::class, 'resolve']);
                Route::post('/recourses/{id}/reject', [AdminRecourseController::class, 'reject']);
            });
        });
    });
});

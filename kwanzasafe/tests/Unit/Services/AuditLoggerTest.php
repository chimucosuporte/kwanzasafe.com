<?php

use App\Services\AuditLogger;

// ---------------------------------------------------------------------------
// AuditLogger nunca lança excepção — contrato crítico de segurança
// ---------------------------------------------------------------------------

it('does not throw when called with null target', function () {
    expect(fn () => AuditLogger::log('test.event', 'auth', 'Test message'))->not->toThrow(Throwable::class);
});

it('does not throw when called without authenticated user', function () {
    expect(fn () => AuditLogger::transaction('test', 'Test transaction log', null, []))->not->toThrow(Throwable::class);
});

it('does not throw on kyc log without user', function () {
    expect(fn () => AuditLogger::kyc('kyc_test', 'Test KYC log', null, []))->not->toThrow(Throwable::class);
});

it('does not throw on admin log', function () {
    expect(fn () => AuditLogger::admin('admin_test', 'Test admin log', []))->not->toThrow(Throwable::class);
});

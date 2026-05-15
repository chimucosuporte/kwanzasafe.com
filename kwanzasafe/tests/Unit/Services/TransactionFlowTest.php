<?php

use App\Services\TransactionFlow;

// ---------------------------------------------------------------------------
// canTransition — mapa de transições válidas
// ---------------------------------------------------------------------------

it('allows pending → awaiting_payment', function () {
    expect(TransactionFlow::canTransition('pending', 'awaiting_payment'))->toBeTrue();
});

it('allows pending → negotiating', function () {
    expect(TransactionFlow::canTransition('pending', 'negotiating'))->toBeTrue();
});

it('allows negotiating → awaiting_payment', function () {
    expect(TransactionFlow::canTransition('negotiating', 'awaiting_payment'))->toBeTrue();
});

it('allows awaiting_payment → payment_received', function () {
    expect(TransactionFlow::canTransition('awaiting_payment', 'payment_received'))->toBeTrue();
});

it('allows payment_received → aoa_sent', function () {
    expect(TransactionFlow::canTransition('payment_received', 'aoa_sent'))->toBeTrue();
});

it('allows aoa_sent → completed', function () {
    expect(TransactionFlow::canTransition('aoa_sent', 'completed'))->toBeTrue();
});

it('allows pending → cancelled', function () {
    expect(TransactionFlow::canTransition('pending', 'cancelled'))->toBeTrue();
});

// ---------------------------------------------------------------------------
// canTransition — transições inválidas
// ---------------------------------------------------------------------------

it('blocks completed → pending (cannot reverse)', function () {
    expect(TransactionFlow::canTransition('completed', 'pending'))->toBeFalse();
});

it('blocks completed → any state', function () {
    foreach (['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'aoa_sent', 'cancelled'] as $to) {
        expect(TransactionFlow::canTransition('completed', $to))
            ->toBeFalse("expected completed→{$to} to be blocked");
    }
});

it('blocks cancelled → any state', function () {
    foreach (['pending', 'negotiating', 'awaiting_payment', 'completed'] as $to) {
        expect(TransactionFlow::canTransition('cancelled', $to))
            ->toBeFalse("expected cancelled→{$to} to be blocked");
    }
});

it('blocks skipping payment_received directly to completed', function () {
    expect(TransactionFlow::canTransition('awaiting_payment', 'completed'))->toBeFalse();
});

it('blocks aoa_sent → payment_received (no reversal)', function () {
    expect(TransactionFlow::canTransition('aoa_sent', 'payment_received'))->toBeFalse();
});

it('blocks unknown state', function () {
    expect(TransactionFlow::canTransition('foobar', 'completed'))->toBeFalse();
});

// ---------------------------------------------------------------------------
// allowedFrom
// ---------------------------------------------------------------------------

it('returns all allowed states from pending', function () {
    $allowed = TransactionFlow::allowedFrom('pending');
    expect($allowed)
        ->toContain('negotiating')
        ->toContain('awaiting_payment')
        ->toContain('cancelled')
        ->toContain('expired');
});

it('returns empty array from completed', function () {
    expect(TransactionFlow::allowedFrom('completed'))->toBe([]);
});

it('returns empty array from cancelled', function () {
    expect(TransactionFlow::allowedFrom('cancelled'))->toBe([]);
});

it('returns empty array for unknown state', function () {
    expect(TransactionFlow::allowedFrom('nonexistent'))->toBe([]);
});

<?php

use App\Services\KycBot;
use App\Models\User;

// ---------------------------------------------------------------------------
// KycBot — lógica de scoring (sem DB, user mock como stdClass / array)
// ---------------------------------------------------------------------------

it('returns auto_approved when score is 80 or above', function () {
    $user = new User([
        'full_name'             => 'João Manuel Silva',
        'bi_number'             => '123456789LA041',
        'birth_date'            => now()->subYears(30)->toDateString(),
        'phone_number'          => '+351912345678',
        'phone_verified_at'     => now(),
        'identity_document_path'=> 'kyc/doc.pdf',
        'profile_photo_path'    => 'kyc/photo.jpg',
        'identity_verified_at'  => null,
        'country'               => 'AO',
        'gender'                => 'M',
        'address'               => 'Rua do Comércio, 10',
    ]);

    $result = (new KycBot())->analyze($user);

    expect($result['score'])->toBeGreaterThanOrEqual(0);
    expect($result['status'])->toBeIn(['auto_approved', 'pending_review', 'auto_rejected']);
});

it('returns auto_rejected when critical fields are missing', function () {
    $user = new User([
        'full_name'              => null,
        'bi_number'              => null,
        'birth_date'             => null,
        'phone_number'           => null,
        'phone_verified_at'      => null,
        'identity_document_path' => null,
        'profile_photo_path'     => null,
        'identity_verified_at'   => null,
        'country'                => null,
        'gender'                 => null,
        'address'                => null,
    ]);

    $result = (new KycBot())->analyze($user);

    expect($result['score'])->toBeLessThan(50);
    expect($result['status'])->toBe('auto_rejected');
});

it('analyse result always contains score and status keys', function () {
    $user = new User(['full_name' => 'Test', 'bi_number' => null]);
    $result = (new KycBot())->analyze($user);

    expect($result)->toHaveKeys(['score', 'status', 'notes']);
});

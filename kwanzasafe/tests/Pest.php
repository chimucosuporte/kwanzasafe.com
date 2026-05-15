<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
| Feature tests: aplica RefreshDatabase (MySQL kwanzasafe_test) + actingAs helper.
| Unit tests:    sem DB — lógica pura.
*/

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
)->in('Feature');

uses(Tests\TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations personalizadas
|--------------------------------------------------------------------------
*/

expect()->extend('toBeStatus', function (string $status) {
    return $this->toHaveKey('status', $status);
});

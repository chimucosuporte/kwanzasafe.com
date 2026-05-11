<?php

/*
|--------------------------------------------------------------------------
| KwanzaSafe — Helpers Globais
|--------------------------------------------------------------------------
| Este ficheiro é registado via composer.json (autoload.files).
| Depois de qualquer alteração: composer dump-autoload
|--------------------------------------------------------------------------
*/

if (!function_exists('ks_file')) {
    /**
     * URL correto para ficheiro em storage/app/public.
     * Funciona em localhost WAMP, php artisan serve, Hostinger, etc.
     *
     * Uso:
     *   ks_file($user->profile_photo_path)
     *   ks_file('kyc/documents/abc.pdf')
     *
     * Devolve null se $path for null/vazio (evita URLs partidos).
     */
    function ks_file(?string $path): ?string
    {
        if (empty($path)) return null;
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        return asset('storage/' . $path);
    }
}

if (!function_exists('ks_is_image')) {
    function ks_is_image(?string $path): bool
    {
        if (empty($path)) return false;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}

if (!function_exists('ks_is_pdf')) {
    function ks_is_pdf(?string $path): bool
    {
        if (empty($path)) return false;
        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }
}

if (!function_exists('ks_route')) {
    /**
     * Gera URL de rota SEGURA com fallback.
     * Evita "Route [xxx] not defined" a partir um ficheiro Blade.
     */
    function ks_route(string $name, $parameters = [], ?string $fallback = '#'): string
    {
        if (\Illuminate\Support\Facades\Route::has($name)) {
            return route($name, $parameters);
        }
        return $fallback;
    }
}
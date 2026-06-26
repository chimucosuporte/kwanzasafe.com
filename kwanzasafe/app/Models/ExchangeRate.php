<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = ['rate', 'is_active', 'currency_from', 'currency_to'];

    /**
     * Taxas activas em cache (TTL curto de 60s) — lidas em cada load do
     * dashboard, da calculadora e da API. Auto-renova; invalidação explícita
     * em forgetActiveCache() após edição de taxas para reflectir de imediato.
     */
    public static function activeCached(): Collection
    {
        return Cache::remember('ks.rates.active', 60, fn () =>
            static::where('is_active', true)->orderBy('currency_from')->get()
        );
    }

    /** Invalida o cache das taxas activas (após criar/editar uma taxa). */
    public static function forgetActiveCache(): void
    {
        Cache::forget('ks.rates.active');
    }
}

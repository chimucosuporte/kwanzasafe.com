<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Models\ExchangeRate;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Taxas de câmbio activas — alimenta a calculadora da app mobile.
 */
class RateController extends Controller
{
    /**
     * Lista as taxas activas (EUR/BRL/USDC → AOA).
     */
    public function index(): AnonymousResourceCollection
    {
        return ExchangeRateResource::collection(ExchangeRate::activeCached());
    }
}

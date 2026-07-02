<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestão de taxas via app admin — espelha o Admin\RateAdminController web.
 */
class RateController extends Controller
{
    public function index(): JsonResponse
    {
        $rates = ExchangeRate::orderBy('currency_from')->get()->map(fn ($r) => $this->item($r));
        return response()->json(['data' => $rates]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'rate'      => ['required', 'numeric', 'min:0.0001'],
            'is_active' => ['required', 'boolean'],
        ]);

        $rate    = ExchangeRate::findOrFail($id);
        $oldRate = $rate->rate;

        $rate->rate      = $data['rate'];
        $rate->is_active = $data['is_active'];
        $rate->save();

        ExchangeRate::forgetActiveCache();

        AuditLogger::admin('rate_updated',
            "Taxa {$rate->currency_from}/AOA alterada de {$oldRate} para {$rate->rate} (app)",
            $rate,
            ['old_rate' => $oldRate, 'new_rate' => $rate->rate, 'is_active' => $rate->is_active]
        );

        return response()->json(['message' => "Taxa de {$rate->currency_from} atualizada.", 'data' => $this->item($rate->fresh())]);
    }

    private function item(ExchangeRate $r): array
    {
        return [
            'id'            => $r->id,
            'currency_from' => $r->currency_from,
            'currency_to'   => $r->currency_to,
            'rate'          => (string) $r->rate,
            'is_active'     => (bool) $r->is_active,
            'updated_at'    => $r->updated_at?->toIso8601String(),
        ];
    }
}

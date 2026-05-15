<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRateRequest;
use App\Models\ExchangeRate;
use App\Services\AuditLogger;

class RateAdminController extends Controller
{
    public function index()
    {
        $rates = ExchangeRate::orderBy('currency_from')->get();
        return view('admin.rates.index', compact('rates'));
    }

    public function edit($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        return view('admin.rates.edit', compact('rate'));
    }

    public function update(UpdateRateRequest $request, $id)
    {
        $rate    = ExchangeRate::findOrFail($id);
        $oldRate = $rate->rate;

        $rate->rate      = $request->rate;
        $rate->is_active = $request->is_active;
        $rate->save();

        AuditLogger::admin('rate_updated',
            "Taxa {$rate->currency_from}/AOA alterada de {$oldRate} para {$rate->rate}",
            $rate,
            ['old_rate' => $oldRate, 'new_rate' => $rate->rate, 'is_active' => $rate->is_active]
        );

        return redirect()->route('admin.rates.index')
            ->with('success', "Taxa de {$rate->currency_from} atualizada com sucesso.");
    }
}

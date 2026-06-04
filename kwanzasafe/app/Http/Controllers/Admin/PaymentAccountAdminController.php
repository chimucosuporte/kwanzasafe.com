<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\PaymentAccount;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Gestão das contas de recepção (dados de pagamento por moeda) — super-admin.
 */
class PaymentAccountAdminController extends Controller
{
    public function index()
    {
        $accounts = PaymentAccount::orderBy('currency')->get();

        // Moedas disponíveis (das taxas) ainda sem conta definida
        $usedCurrencies = $accounts->pluck('currency')->all();
        $availableCurrencies = ExchangeRate::select('currency_from')
            ->distinct()
            ->pluck('currency_from')
            ->reject(fn ($c) => in_array($c, $usedCurrencies, true))
            ->values();

        return view('admin.payment-accounts.index', compact('accounts', 'availableCurrencies'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, null);

        $account = PaymentAccount::create($data);

        AuditLogger::admin('payment_account_created',
            "Conta de recepção criada para {$account->currency}",
            null,
            ['currency' => $account->currency, 'by' => Auth::id()]
        );

        return back()->with('success', "Conta de recepção {$account->currency} criada.");
    }

    public function update(Request $request, $id)
    {
        $account = PaymentAccount::findOrFail($id);
        $data = $this->validateData($request, $account->id);

        $account->update($data);

        AuditLogger::admin('payment_account_updated',
            "Conta de recepção {$account->currency} actualizada",
            null,
            ['currency' => $account->currency, 'by' => Auth::id()]
        );

        return back()->with('success', "Conta de recepção {$account->currency} actualizada.");
    }

    public function destroy($id)
    {
        $account = PaymentAccount::findOrFail($id);
        $currency = $account->currency;
        $account->delete();

        AuditLogger::admin('payment_account_deleted',
            "Conta de recepção {$currency} eliminada",
            null,
            ['currency' => $currency, 'by' => Auth::id()]
        );

        return back()->with('success', "Conta de recepção {$currency} eliminada.");
    }

    private function validateData(Request $request, ?int $ignoreId): array
    {
        $validated = $request->validate([
            'currency'     => ['required', 'string', 'max:10', Rule::unique('payment_accounts', 'currency')->ignore($ignoreId)],
            'holder'       => ['required', 'string', 'max:191'],
            'identifier'   => ['required', 'string', 'max:255'],
            'network'      => ['nullable', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        $validated['currency']  = strtoupper($validated['currency']);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}

<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\PaymentAccount;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Contas de recepção (dados de pagamento por moeda) via app admin — super-admin.
 * Espelha o Admin\PaymentAccountAdminController web.
 */
class PaymentAccountController extends Controller
{
    public function index(): JsonResponse
    {
        $accounts = PaymentAccount::orderBy('currency')->get();
        $used = $accounts->pluck('currency')->all();
        $available = ExchangeRate::select('currency_from')->distinct()->pluck('currency_from')
            ->reject(fn ($c) => in_array($c, $used, true))->values();

        return response()->json([
            'data'                 => $accounts->map(fn ($a) => $this->item($a)),
            'available_currencies' => $available,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request, null);
        $account = PaymentAccount::create($data);

        AuditLogger::admin('payment_account_created', "Conta de recepção criada para {$account->currency} (app)", null, ['currency' => $account->currency, 'by' => $request->user()->id]);

        return response()->json(['message' => "Conta {$account->currency} criada.", 'data' => $this->item($account)], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $account = PaymentAccount::findOrFail($id);
        $account->update($this->validated($request, $account->id));

        AuditLogger::admin('payment_account_updated', "Conta de recepção {$account->currency} actualizada (app)", null, ['currency' => $account->currency, 'by' => $request->user()->id]);

        return response()->json(['message' => "Conta {$account->currency} actualizada.", 'data' => $this->item($account->fresh())]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $account = PaymentAccount::findOrFail($id);
        $currency = $account->currency;
        $account->delete();

        AuditLogger::admin('payment_account_deleted', "Conta de recepção {$currency} eliminada (app)", null, ['currency' => $currency, 'by' => $request->user()->id]);

        return response()->json(['message' => "Conta {$currency} eliminada."]);
    }

    private function validated(Request $request, ?int $ignoreId): array
    {
        $data = $request->validate([
            'currency'     => ['required', 'string', 'max:10', Rule::unique('payment_accounts', 'currency')->ignore($ignoreId)],
            'holder'       => ['required', 'string', 'max:191'],
            'identifier'   => ['required', 'string', 'max:255'],
            'network'      => ['nullable', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'is_active'    => ['nullable', 'boolean'],
        ]);
        $data['currency']  = strtoupper($data['currency']);
        $data['is_active'] = $request->boolean('is_active');
        return $data;
    }

    private function item(PaymentAccount $a): array
    {
        return [
            'id'           => $a->id,
            'currency'     => $a->currency,
            'holder'       => $a->holder,
            'identifier'   => $a->identifier,
            'network'      => $a->network,
            'instructions' => $a->instructions,
            'is_active'    => (bool) $a->is_active,
        ];
    }
}

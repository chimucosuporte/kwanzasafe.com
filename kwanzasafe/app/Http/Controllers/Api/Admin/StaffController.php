<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Gestão de funcionários de suporte via app admin — super-admin.
 * Espelha o Admin\StaffAdminController web.
 */
class StaffController extends Controller
{
    private const OPEN = ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'processing', 'aoa_sent'];

    public function index(): JsonResponse
    {
        $staff = User::where('is_admin', true)->where('is_super_admin', false)
            ->withCount(['assignedTransactions as open_tickets' => fn ($q) => $q->whereIn('status', self::OPEN)])
            ->orderByDesc('is_active')->orderBy('full_name')->get()
            ->map(fn ($u) => $this->item($u));

        $superAdmins = User::where('is_super_admin', true)->orderBy('full_name')->get()
            ->map(fn ($u) => ['id' => $u->id, 'full_name' => $u->full_name, 'email' => $u->email]);

        return response()->json(['staff' => $staff->values(), 'super_admins' => $superAdmins->values()]);
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $user = User::create([
            'full_name'      => $request->full_name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'is_admin'       => true,
            'is_super_admin' => false,
            'is_active'      => true,
        ]);

        $user->forceFill(['email_verified_at' => now(), 'status' => 'active', 'country' => 'AO'])->save();

        AuditLogger::admin('staff_created', "Conta de suporte criada: {$user->email} (app)", $user, ['created_by' => $request->user()->id]);

        return response()->json(['message' => "Conta de suporte criada para {$user->full_name}.", 'data' => $this->item($user->fresh())], 201);
    }

    public function toggleActive(Request $request, $id): JsonResponse
    {
        $user = $this->findStaff($id);
        $user->update(['is_active' => ! $user->is_active]);
        $label = $user->is_active ? 'reactivada' : 'desactivada';

        AuditLogger::admin('staff_' . ($user->is_active ? 'activated' : 'deactivated'), "Conta de suporte {$label}: {$user->email} (app)", $user, ['by' => $request->user()->id]);

        return response()->json(['message' => "Conta de {$user->full_name} {$label}.", 'data' => $this->item($user->fresh())]);
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        $user = $this->findStaff($id);
        $name = $user->full_name;

        $reassigned = Transaction::where('assigned_admin', $user->id)->whereIn('status', self::OPEN)->update(['assigned_admin' => null]);
        $user->delete();

        AuditLogger::admin('staff_deleted', "Conta de suporte eliminada: {$user->email} (app)", $user, ['by' => $request->user()->id, 'tickets_released' => $reassigned]);

        return response()->json(['message' => "Conta de suporte de {$name} eliminada." . ($reassigned ? " {$reassigned} tíquete(s) devolvido(s) à fila." : '')]);
    }

    private function findStaff($id): User
    {
        return User::where('is_admin', true)->where('is_super_admin', false)->findOrFail($id);
    }

    private function item(User $u): array
    {
        return [
            'id'           => $u->id,
            'full_name'    => $u->full_name,
            'email'        => $u->email,
            'is_active'    => (bool) $u->is_active,
            'open_tickets' => (int) ($u->open_tickets ?? 0),
            'created_at'   => $u->created_at?->toIso8601String(),
        ];
    }
}

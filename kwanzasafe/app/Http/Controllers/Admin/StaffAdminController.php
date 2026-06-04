<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Gestão de funcionários de suporte — exclusivo do Super-Admin.
 *
 * O super-admin pode registar, desactivar/reactivar e eliminar (soft delete)
 * contas de suporte. Contas de suporte não passam pelo fluxo KYC de cliente.
 */
class StaffAdminController extends Controller
{
    /** Estados de tíquete considerados "abertos" (a precisar de atenção). */
    private const OPEN_STATUSES = ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'processing', 'aoa_sent'];

    public function index()
    {
        $staff = User::where('is_admin', true)
            ->where('is_super_admin', false)
            ->withCount(['assignedTransactions as open_tickets' => fn ($q) =>
                $q->whereIn('status', self::OPEN_STATUSES)
            ])
            ->orderByDesc('is_active')
            ->orderBy('full_name')
            ->get();

        $superAdmins = User::where('is_super_admin', true)
            ->orderBy('full_name')
            ->get();

        return view('admin.staff.index', compact('staff', 'superAdmins'));
    }

    public function store(StoreStaffRequest $request)
    {
        $user = User::create([
            'full_name'         => $request->full_name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password),
            'is_admin'          => true,
            'is_super_admin'    => false,
            'is_active'         => true,
        ]);

        // email_verified_at / status não são fillable — definidos explicitamente.
        // Staff não passa por verificação de email nem KYC.
        $user->forceFill([
            'email_verified_at' => now(),
            'status'            => 'active',
            'country'           => 'AO',
        ])->save();

        AuditLogger::admin('staff_created',
            "Conta de suporte criada: {$user->email}",
            $user,
            ['created_by' => Auth::id()]
        );

        return back()->with('success', "Conta de suporte criada para {$user->full_name}.");
    }

    public function toggleActive($id)
    {
        $user = $this->findStaff($id);

        $user->update(['is_active' => ! $user->is_active]);
        $label = $user->is_active ? 'reactivada' : 'desactivada';

        AuditLogger::admin('staff_' . ($user->is_active ? 'activated' : 'deactivated'),
            "Conta de suporte {$label}: {$user->email}",
            $user,
            ['by' => Auth::id()]
        );

        return back()->with('success', "Conta de {$user->full_name} {$label}.");
    }

    public function destroy($id)
    {
        $user  = $this->findStaff($id);
        $email = $user->email;
        $name  = $user->full_name;

        // Libertar tíquetes abertos atribuídos a este agente antes de eliminar.
        $reassigned = Transaction::where('assigned_admin', $user->id)
            ->whereIn('status', self::OPEN_STATUSES)
            ->update(['assigned_admin' => null]);

        $user->delete(); // soft delete (deleted_at)

        AuditLogger::admin('staff_deleted',
            "Conta de suporte eliminada: {$email}",
            $user,
            ['by' => Auth::id(), 'tickets_released' => $reassigned]
        );

        return back()->with('success', "Conta de suporte de {$name} eliminada." .
            ($reassigned ? " {$reassigned} tíquete(s) devolvido(s) à fila." : ''));
    }

    /**
     * Garante que o super-admin só gere contas de SUPORTE
     * (não outros super-admins nem clientes).
     */
    private function findStaff($id): User
    {
        return User::where('is_admin', true)
            ->where('is_super_admin', false)
            ->findOrFail($id);
    }
}

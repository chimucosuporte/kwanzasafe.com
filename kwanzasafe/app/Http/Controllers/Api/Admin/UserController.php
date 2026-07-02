<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Gestão de utilizadores via app admin — espelha o Admin\UserAdminController web.
 */
class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $kyc    = $request->query('kyc', 'all');
        $search = $request->query('q', '');

        $query = User::orderByDesc('created_at');

        if ($kyc === 'approved') {
            $query->whereNotNull('identity_verified_at');
        } elseif ($kyc === 'pending') {
            $query->whereNotNull('identity_document_path')->whereNull('identity_verified_at');
        } elseif ($kyc === 'none') {
            $query->whereNull('identity_document_path');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('bi_number', 'like', "%{$search}%");
            });
        }

        $page = $query->paginate(20);

        return response()->json([
            'data' => collect($page->items())->map(fn ($u) => $this->item($u)),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    public function show($id): JsonResponse
    {
        $user = User::findOrFail($id);

        $stats = [
            'total'     => Transaction::where('user_id', $id)->count(),
            'completed' => Transaction::where('user_id', $id)->where('status', 'completed')->count(),
            'active'    => Transaction::where('user_id', $id)->whereIn('status', ['pending', 'negotiating', 'awaiting_payment', 'payment_received', 'aoa_sent'])->count(),
            'cancelled' => Transaction::where('user_id', $id)->whereIn('status', ['cancelled', 'expired'])->count(),
        ];

        return response()->json(['data' => array_merge($this->detail($user), ['tx_stats' => $stats])]);
    }

    public function toggleAdmin(Request $request, $id): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === $request->user()->id) {
            return response()->json(['message' => 'Não podes alterar o teu próprio papel.'], 422);
        }

        $user->update(['is_admin' => ! $user->is_admin]);
        $action = $user->is_admin ? 'promovido a admin' : 'removido de admin';

        AuditLogger::admin("user_{$action}", "Utilizador {$user->email} foi {$action} (app)", $user);

        return response()->json(['message' => "Utilizador {$action}.", 'data' => $this->detail($user->fresh())]);
    }

    private function item(User $u): array
    {
        return [
            'id'          => $u->id,
            'full_name'   => $u->full_name,
            'email'       => $u->email,
            'is_admin'    => (bool) $u->is_admin,
            'is_verified' => (bool) $u->identity_verified_at,
            'kyc_pending' => (bool) ($u->identity_document_path && ! $u->identity_verified_at),
            'created_at'  => $u->created_at?->toIso8601String(),
        ];
    }

    private function detail(User $u): array
    {
        return array_merge($this->item($u), [
            'is_super_admin'   => (bool) $u->is_super_admin,
            'role_label'       => method_exists($u, 'roleLabel') ? $u->roleLabel() : null,
            'phone_number'     => $u->phone_number,
            'phone_verified'   => (bool) $u->phone_verified_at,
            'email_verified'   => (bool) $u->email_verified_at,
            'bi_number'        => $u->bi_number,
            'province'         => $u->province,
            'country'          => $u->country,
            'balance'          => (string) $u->balance,
            'kyc_score'        => $u->kyc_score,
            'created_at_human' => $u->created_at?->toIso8601String(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $kycFilter = $request->query('kyc', 'all');
        $search    = $request->query('q', '');

        $query = User::orderBy('created_at', 'desc');

        if ($kycFilter === 'approved') {
            $query->whereNotNull('identity_verified_at');
        } elseif ($kycFilter === 'pending') {
            $query->whereNotNull('identity_document_path')->whereNull('identity_verified_at');
        } elseif ($kycFilter === 'none') {
            $query->whereNull('identity_document_path');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('bi_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users', 'kycFilter', 'search'));
    }

    public function show($id)
    {
        $user = User::findOrFail($id);

        $transactions = Transaction::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        $txStats = [
            'total'     => Transaction::where('user_id', $id)->count(),
            'completed' => Transaction::where('user_id', $id)->where('status', 'completed')->count(),
            'active'    => Transaction::where('user_id', $id)->whereIn('status', ['pending','negotiating','awaiting_payment','payment_received','aoa_sent'])->count(),
            'cancelled' => Transaction::where('user_id', $id)->whereIn('status', ['cancelled','expired'])->count(),
        ];

        return view('admin.users.show', compact('user', 'transactions', 'txStats'));
    }

    public function toggleAdmin($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Não podes alterar o teu próprio papel.');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        $action = $user->is_admin ? 'promovido a admin' : 'removido de admin';

        AuditLogger::admin("user_{$action}", "Utilizador {$user->email} foi {$action}", $user);

        return back()->with('success', "Utilizador {$action} com sucesso.");
    }
}

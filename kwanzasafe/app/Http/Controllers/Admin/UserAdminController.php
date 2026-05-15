<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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
}

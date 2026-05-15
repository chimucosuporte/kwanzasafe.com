<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Transaction;

class MessageAdminController extends Controller
{
    public function unread()
    {
        $transactionIds = ChatMessage::whereHas('sender', fn($q) => $q->where('is_admin', false))
            ->where('is_read', false)
            ->pluck('transaction_id')
            ->unique()
            ->values();

        $transactions = Transaction::with(['user'])
            ->whereIn('id', $transactionIds)
            ->withCount(['chatMessages as unread_count' => function ($q) {
                $q->whereHas('sender', fn($qq) => $qq->where('is_admin', false))
                  ->where('is_read', false);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.messages.unread', compact('transactions'));
    }
}

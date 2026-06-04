<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Servidor autenticado de ficheiros privados (KYC, comprovativos, anexos).
 *
 * Os ficheiros sensíveis ficam no disco privado ('local', storage/app) e
 * NUNCA são acessíveis directamente por URL. Esta rota verifica a autorização
 * antes de os transmitir:
 *   - staff (suporte/super-admin): acesso a qualquer ficheiro;
 *   - cliente: apenas aos seus próprios documentos KYC e aos anexos das suas
 *     transações.
 */
class FileController extends Controller
{
    private const DISK = 'local';

    public function show(Request $request, string $path)
    {
        $user = Auth::user();
        $path = ltrim($path, '/');

        // Defesa contra path traversal.
        abort_if(str_contains($path, '..'), 404);

        if (! $user->isStaff() && ! $this->clientOwns($user, $path)) {
            abort(403);
        }

        abort_unless(Storage::disk(self::DISK)->exists($path), 404);

        // Inline (imagens/PDF abrem no browser); cookie de sessão autentica.
        return Storage::disk(self::DISK)->response($path);
    }

    private function clientOwns($user, string $path): bool
    {
        if ($path !== '' && ($path === $user->identity_document_path || $path === $user->profile_photo_path)) {
            return true;
        }

        return ChatMessage::where('file_path', $path)
            ->whereHas('transaction', fn ($q) => $q->where('user_id', $user->id))
            ->exists();
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\AuditLogger;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * Perfil do utilizador via API mobile.
 * Reutiliza ProfileUpdateRequest (web). A verificação da palavra-passe atual é
 * feita manualmente (Hash::check) por o guard ser stateless/sanctum.
 */
class ProfileController extends Controller
{
    /** Actualiza nome/email. Mudar o email reinicia a verificação. */
    public function update(ProfileUpdateRequest $request): JsonResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        AuditLogger::profile('updated', 'Perfil actualizado (mobile)', $user, ['channel' => 'mobile']);

        return response()->json([
            'message' => 'Perfil actualizado.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /** Pede a alteração de email: envia código ao email atual e ao novo. */
    public function requestEmailChange(Request $request, OtpService $otp): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $user = $request->user();

        if (strtolower(trim($data['email'])) === strtolower(trim($user->email))) {
            throw ValidationException::withMessages(['email' => 'Esse já é o teu email atual.']);
        }

        $res = $otp->sendEmailChangeOtp($user, trim($data['email']));

        if (! ($res['success'] ?? false)) {
            return response()->json(['message' => $res['message']], 422);
        }

        return response()->json(['message' => $res['message']]);
    }

    /** Confirma a alteração de email com os dois códigos (atual + novo). */
    public function confirmEmailChange(Request $request, OtpService $otp): JsonResponse
    {
        $data = $request->validate([
            'email'        => ['required', 'email', 'unique:users,email'],
            'code_current' => ['required', 'string'],
            'code_new'     => ['required', 'string'],
        ]);

        $user = $request->user();

        $res = $otp->verifyEmailChangeOtp($user, trim($data['email']), $data['code_current'], $data['code_new']);

        if (! ($res['success'] ?? false)) {
            throw ValidationException::withMessages([
                ($res['field'] ?? 'code_current') => $res['message'] ?? 'Códigos inválidos.',
            ]);
        }

        $user->email = trim($data['email']);
        $user->email_verified_at = now(); // provou o novo email com o código
        $user->save();

        AuditLogger::profile('email_changed', 'Email alterado com confirmação dupla (mobile)', $user, ['channel' => 'mobile']);

        return response()->json([
            'message' => 'Email alterado com sucesso.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /** Actualiza a foto de perfil (avatar). Guarda em disco privado. */
    public function updatePhoto(Request $request): JsonResponse
    {
        $request->validate(['photo' => 'required|file|mimes:jpg,jpeg,png|max:5120']);

        $user = $request->user();

        if ($user->avatar_path && Storage::disk('local')->exists($user->avatar_path)) {
            Storage::disk('local')->delete($user->avatar_path);
        }

        $user->avatar_path = $request->file('photo')->store('avatars', 'local');
        $user->save();

        AuditLogger::profile('avatar_updated', 'Foto de perfil actualizada (mobile)', $user, ['channel' => 'mobile']);

        return response()->json([
            'message' => 'Foto de perfil actualizada.',
            'user'    => new UserResource($user->fresh()),
        ]);
    }

    /** Altera a palavra-passe (exige a atual). */
    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'A palavra-passe atual está incorreta.',
            ]);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        // Revoga os outros tokens, mantendo a sessão atual.
        $currentId = optional($user->currentAccessToken())->id;
        $user->tokens()->where('id', '!=', $currentId)->delete();

        AuditLogger::profile('password_changed', 'Palavra-passe alterada (mobile)', $user, ['channel' => 'mobile']);

        return response()->json(['message' => 'Palavra-passe alterada com sucesso.']);
    }

    /** Elimina definitivamente a própria conta (exige palavra-passe). */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['password' => ['required', 'string']]);

        $user = $request->user();

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'A palavra-passe está incorreta.',
            ]);
        }

        AuditLogger::profile('account_deleted', 'Conta eliminada pelo próprio (mobile)', $user, ['channel' => 'mobile']);

        $user->tokens()->delete();
        // Hard delete: o cliente que elimina a própria conta quer os dados removidos.
        $user->forceDelete();

        return response()->json(['message' => 'Conta eliminada.']);
    }
}

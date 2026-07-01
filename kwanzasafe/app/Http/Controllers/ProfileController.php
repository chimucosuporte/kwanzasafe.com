<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AuditLogger;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // O email só pode ser alterado pelo fluxo com dupla confirmação (OTP).
        // Aqui aplicamos apenas o nome; ignoramos qualquer email submetido.
        $request->user()->fill(['name' => $validated['name']]);
        $request->user()->save();

        return Redirect::route('profile.edit')->with('success', 'Perfil actualizado com sucesso.');
    }

    /**
     * Passo 1 do fluxo seguro de alteração de email:
     * envia um código ao email ATUAL e outro ao email NOVO.
     */
    public function requestEmailChange(Request $request, OtpService $otp): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ]);

        $user     = $request->user();
        $newEmail = strtolower(trim($data['email']));

        if ($newEmail === strtolower(trim($user->email))) {
            return back()->withErrors(['email' => 'Esse já é o teu email atual.'])->withInput();
        }

        $res = $otp->sendEmailChangeOtp($user, $newEmail, $request->ip());

        if (! ($res['success'] ?? false)) {
            return back()->withErrors(['email' => $res['message'] ?? 'Não foi possível enviar os códigos.'])->withInput();
        }

        // Guarda o email pendente para o passo 2 (mostra os campos de código).
        $request->session()->put('pending_email_change', $newEmail);

        return back()->with('success', $res['message'] ?? 'Enviámos um código ao teu email atual e ao novo email.');
    }

    /**
     * Passo 2: confirma a alteração com os dois códigos (atual + novo).
     */
    public function confirmEmailChange(Request $request, OtpService $otp): RedirectResponse
    {
        $data = $request->validate([
            'email'        => ['required', 'email', 'unique:users,email'],
            'code_current' => ['required', 'string'],
            'code_new'     => ['required', 'string'],
        ]);

        $user     = $request->user();
        $newEmail = strtolower(trim($data['email']));

        $res = $otp->verifyEmailChangeOtp($user, $newEmail, $data['code_current'], $data['code_new']);

        if (! ($res['success'] ?? false)) {
            return back()->withErrors([
                ($res['field'] ?? 'code_current') => $res['message'] ?? 'Códigos inválidos.',
            ]);
        }

        $user->email = $newEmail;
        $user->email_verified_at = now(); // provou o novo email com o código
        $user->save();

        $request->session()->forget('pending_email_change');

        AuditLogger::profile('email_changed', 'Email alterado com confirmação dupla (web)', $user, ['channel' => 'web']);

        return Redirect::route('profile.edit')->with('success', 'Email alterado com sucesso.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Hard delete: o cliente que elimina a própria conta quer os dados
        // removidos definitivamente (privacidade). O soft delete fica reservado
        // à eliminação de contas de staff pelo super-admin.
        $user->forceDelete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

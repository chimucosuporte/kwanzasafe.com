<div class="pf-card__head">
    <div class="pf-card__title">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Informação de Perfil
    </div>
    <p class="pf-card__sub">Actualiza o teu nome e endereço de email.</p>
</div>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="pf-field">
        <label for="name" class="pf-label">Nome completo</label>
        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
               required autofocus autocomplete="name"
               class="pf-input @error('name') error @enderror">
        @error('name') <div class="pf-err">{{ $message }}</div> @enderror
    </div>

    <div class="pf-field">
        <label for="email" class="pf-label">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
               readonly autocomplete="username"
               class="pf-input" style="background:var(--ks-gray-100); color:var(--ks-gray-600); cursor:not-allowed;">
        <div class="pf-err" style="color:var(--ks-gray-500);">Para alterar o email, usa a secção “Alterar email” abaixo (com confirmação por código).</div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="pf-status warn" style="margin-top:0.75rem; margin-bottom:0;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01" stroke-linecap="round"/><circle cx="12" cy="12" r="9"/></svg>
                <span>
                    O teu email ainda não está verificado.
                    <button form="send-verification" style="background:none;border:none;padding:0;color:#92400e;font-weight:800;text-decoration:underline;cursor:pointer;font-family:inherit;font-size:inherit;">
                        Reenviar email de verificação
                    </button>
                </span>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="pf-err" style="color:var(--ks-green-dark);">Foi enviado um novo link de verificação para o teu email.</div>
            @endif
        @endif
    </div>

    <div class="pf-actions">
        <button type="submit" class="pf-btn">Guardar</button>

        @if (session('status') === 'profile-updated')
            <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="pf-saved">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Guardado.
            </span>
        @endif
    </div>
</form>

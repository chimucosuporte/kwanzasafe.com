<div class="pf-card__head">
    <div class="pf-card__title">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Alterar Password
    </div>
    <p class="pf-card__sub">Usa uma password longa e aleatória para manteres a conta segura.</p>
</div>

<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="pf-field">
        <label for="update_password_current_password" class="pf-label">Password actual</label>
        <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
               class="pf-input @error('current_password', 'updatePassword') error @enderror">
        @error('current_password', 'updatePassword') <div class="pf-err">{{ $message }}</div> @enderror
    </div>

    <div class="pf-field">
        <label for="update_password_password" class="pf-label">Nova password</label>
        <input id="update_password_password" name="password" type="password" autocomplete="new-password"
               class="pf-input @error('password', 'updatePassword') error @enderror">
        @error('password', 'updatePassword') <div class="pf-err">{{ $message }}</div> @enderror
    </div>

    <div class="pf-field">
        <label for="update_password_password_confirmation" class="pf-label">Confirmar nova password</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
               class="pf-input @error('password_confirmation', 'updatePassword') error @enderror">
        @error('password_confirmation', 'updatePassword') <div class="pf-err">{{ $message }}</div> @enderror
    </div>

    <div class="pf-actions">
        <button type="submit" class="pf-btn">Guardar</button>

        @if (session('status') === 'password-updated')
            <span x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="pf-saved">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Guardado.
            </span>
        @endif
    </div>
</form>

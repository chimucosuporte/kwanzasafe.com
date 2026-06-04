<div class="pf-card__head">
    <div class="pf-card__title">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Eliminar Conta
    </div>
    <p class="pf-card__sub">Depois de eliminada, todos os dados e recursos da conta são apagados permanentemente. Antes de continuar, descarrega qualquer informação que queiras guardar.</p>
</div>

<button type="button" class="pf-btn danger"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
    Eliminar a minha conta
</button>

<x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
    <form method="post" action="{{ route('profile.destroy') }}" style="padding:1.5rem;">
        @csrf
        @method('delete')

        <div class="pf-card__title" style="font-size:1.05rem; margin-bottom:0.25rem;">
            Tens a certeza que queres eliminar a conta?
        </div>
        <p class="pf-card__sub" style="margin-bottom:1.25rem;">
            Esta acção é irreversível. Todos os dados serão apagados permanentemente. Introduz a tua password para confirmar.
        </p>

        <div class="pf-field">
            <label for="password" class="pf-label" style="position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0 0 0 0);">Password</label>
            <input id="password" name="password" type="password" placeholder="A tua password"
                   class="pf-input @error('password', 'userDeletion') error @enderror">
            @error('password', 'userDeletion') <div class="pf-err">{{ $message }}</div> @enderror
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.5rem; margin-top:1.25rem;">
            <button type="button" class="pf-btn ghost" x-on:click="$dispatch('close')">Cancelar</button>
            <button type="submit" class="pf-btn danger">Eliminar Conta</button>
        </div>
    </form>
</x-modal>

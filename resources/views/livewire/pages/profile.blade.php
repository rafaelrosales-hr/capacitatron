<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

new #[Layout('layouts.app')] class extends Component {

    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', 'confirmed', Password::defaults()],
            ], [
                'current_password.current_password' => 'La contraseña actual es incorrecta.',
            ]);
        } catch (ValidationException $e) {
            $this->dispatch(
                'toast',
                title: 'No se pudo actualizar',
                message: 'Revisa los datos ingresados e intenta de nuevo.'
            );

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch(
            'toast',
            title: 'Contraseña actualizada',
            message: 'Tu contraseña se cambió correctamente.'
        );

        $this->js(
            "setTimeout(() => { window.location.href = '" . route('dashboard') . "' }, 3000)"
        );
    }
}; ?>

<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 font-sans">

    <div class="bg-background border border-border rounded-2xl overflow-hidden shadow-sm mb-6">

        <div class="h-48 sm:h-60 w-full relative bg-gradient-to-r from-blue-500 to-indigo-600">

            <img src="{{ asset('/assets/img/banner1.jpg') }}" alt="Banner de perfil" class="w-full h-full object-cover">
        </div>

        <div class="px-6 pb-6 text-center relative">
            {{-- <div class="inline-block -mt-16 sm:-mt-20 relative z-10">
                <!-- Reemplaza con la foto del usuario -->
                <img src="{{ asset('/avatar.jpg') }}" alt="{{ Auth::user()->name }}" class="w-32 h-32 rounded-full border-4 border-background object-cover shadow-md bg-zinc-200">
        </div> --}}

        <h1 class="text-2xl font-syne font-bold text-text mt-4">{{ Auth::user()->name }} {{ Auth::user()->surname }} {{ Auth::user()->second_surname }}</h1>
        <p class="text-sm font-montserrat text-text-muted mt-1">
            {{ Auth::user()->major_description }} <span class="mx-1.5">•</span> {{ Auth::user()->state}}, {{ Auth::user()->municipality }}
        </p>
    </div>
</div>
<div class="grid grid-cols-1 items-start">
    <div class="bg-background border border-border rounded-xl p-6 shadow-sm">
        <h2 class="text-lg font-syne font-bold text-text mb-1">Cambio de contraseña</h2>
        <p class="text-xs font-montserrat text-text-muted mb-6 leading-relaxed">
            Asegúrate de usar una contraseña larga y segura para proteger tu cuenta de accesos no autorizados.
        </p>

        <form wire:submit="updatePassword" class="space-y-4">
            <div x-data="{ show: false }">
                <label for="current_password" class="block text-xs font-montserrat font-medium  text-text mb-1.5">
                    Contraseña actual
                </label>
                <div class="relative" style="position: relative;">
                    <input
                        id="current_password"
                        :type="show ? 'text' : 'password'"
                        wire:model="current_password"
                        autocomplete="current-password"
                        style="width: 100%; padding-right: 2.5rem;"
                        class="font-montserrat rounded-lg border border-border bg-background text-text px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors" />
                    <button
                        type="button"
                        @click="show = !show"
                        tabindex="-1"
                        style="position: absolute; top: 0; bottom: 0; right: 0; display: flex; align-items: center; padding: 0 0.75rem;"
                        class="text-text-muted hover:text-text transition-colors cursor-pointer">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5a10.523 10.523 0 004.293-.905M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 4.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('current_password')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ show: false }">
                <label for="password" class="block text-xs font-medium font-montserrat text-text mb-1.5">
                    Nueva contraseña
                </label>
                <div class="relative" style="position: relative;">
                    <input
                        id="password"
                        :type="show ? 'text' : 'password'"
                        wire:model="password"
                        autocomplete="new-password"
                        style="width: 100%; padding-right: 2.5rem;"
                        class="font-montserrat rounded-lg border border-border bg-background text-text px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors" />
                    <button
                        type="button"
                        @click="show = !show"
                        tabindex="-1"
                        style="position: absolute; top: 0; bottom: 0; right: 0; display: flex; align-items: center; padding: 0 0.75rem;"
                        class="text-text-muted hover:text-text transition-colors cursor-pointer">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5a10.523 10.523 0 004.293-.905M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 4.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('password')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ show: false }">
                <label for="password_confirmation" class="block text-xs font-montserrat font-medium text-text mb-1.5">
                    Confirmar nueva contraseña
                </label>
                <div class="relative" style="position: relative;">
                    <input
                        id="password_confirmation"
                        :type="show ? 'text' : 'password'"
                        wire:model="password_confirmation"
                        autocomplete="new-password"
                        style="width: 100%; padding-right: 2.5rem;"
                        class="font-montserrat rounded-lg border border-border bg-background text-text px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent/50 focus:border-accent transition-colors" />
                    <button
                        type="button"
                        @click="show = !show"
                        tabindex="-1"
                        style="position: absolute; top: 0; bottom: 0; right: 0; display: flex; align-items: center; padding: 0 0.75rem;"
                        class="text-text-muted hover:text-text transition-colors cursor-pointer">
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5a10.523 10.523 0 004.293-.905M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 4.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="updatePassword"
                    class="w-full font-montserrat px-4 py-2.5 rounded-lg bg-accent text-white text-sm font-semibold hover:opacity-90 active:scale-[0.98] transition-all cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="updatePassword">Guardar contraseña</span>
                    <span wire:loading wire:target="updatePassword">Guardando...</span>
                </button>
            </div>
        </form>
    </div>

</div>
</div>
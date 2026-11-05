<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.auth')] class extends Component {
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        try {
            $this->form->authenticate();
        } catch (ValidationException $e) {
            $this->dispatch(
                'toast',
                title: 'Error al iniciar sesión',
                message: 'Las credenciales no coinciden con nuestros registros.'
            );

            throw $e;
        }

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<section class="w-full mx-auto p-6 md:p-8 rounded-2xl transition-colors font-montserrat" x-data="{ showPassword: false }">
    <h1 class="text-2xl md:text-3xl text-text-muted font-syne font-semibold text-text mb-2">
        Acceso Portal de Capacitaciones de HR Media
    </h1>

    {{-- <p class="text-xs md:text-sm text-text-muted mb-6">
        Accesa con tu credenciales del <span class="font-bold text-accent">PMS CPM</span>
    </p> --}}

    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
    <ul class="text-red-500 text-xs mb-4 list-none p-0">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    @endif

    <!-- Botón de Iniciar Sesión con Google -->
    <a href="{{ route('auth.google') }}"
        class="w-full flex items-center justify-center gap-3 bg-background/60 hover:bg-surface-hover border border-border text-text font-medium py-2.5 px-4 rounded-xl backdrop-blur-md transition-all duration-200 shadow-sm hover:shadow active:scale-[0.98] mb-6 cursor-pointer">
        <svg class="w-5 h-5" viewBox="0 0 24 24">
            <path fill="#4285F4"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z">
            </path>
            <path fill="#34A853"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z">
            </path>
            <path fill="#FBBC05"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z">
            </path>
            <path fill="#EA4335"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z">
            </path>
        </svg>
        <span class="text-xs md:text-sm text-accent font-semibold">Continuar con Google</span>
    </a>

    <!-- Separador -->
    <div class="relative flex items-center justify-center mb-6">
        <div class="w-full border-t border-border/60"></div>
        <span
            class="absolute bg-background/80 px-3 text-[11px] uppercase tracking-wider text-text-muted backdrop-blur-md">
            o con tu email
        </span>
    </div>

    <form wire:submit="login" id="loginForm" class="space-y-4">
        <!-- Email -->
        <div
            class="border border-border/80 bg-background/50 backdrop-blur-md rounded-xl px-3 py-2 w-full focus-within:border-accent focus-within:ring-1 focus-within:ring-accent transition-all">
            <label for="email"
                class="block text-[11px] font-semibold text-accent mb-0.5 font-kanit tracking-wide uppercase">
                Email <span class="text-accent">*</span>
            </label>
            <input wire:model="form.email" type="email" id="email" name="email"
                placeholder="usuario@hrmedia.com.mx" autocomplete="email" required autofocus
                class="w-full bg-transparent text-text placeholder-text-muted outline-none border-0 focus:ring-0 shadow-none p-0 text-xs md:text-sm" />
        </div>

        <!-- Contraseña -->
        <div
            class="border border-border/80 bg-background/50 backdrop-blur-md rounded-xl px-3 py-2 w-full focus-within:border-accent focus-within:ring-1 focus-within:ring-accent transition-all relative">
            <label for="password"
                class="block text-[11px] font-semibold text-accent mb-0.5 font-kanit tracking-wide uppercase">
                Contraseña <span class="text-accent">*</span>
                @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" wire:navigate
                    class="float-right normal-case text-accent hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
                @endif
            </label>
            <div class="flex items-center">
                <input wire:model="form.password" :type="showPassword ? 'text' : 'password'" id="password"
                    name="password" placeholder="••••••••" autocomplete="current-password" required
                    class="w-full bg-transparent text-text placeholder-text-muted outline-none border-0 focus:ring-0 shadow-none p-0 text-xs md:text-sm" />
                <button type="button" @click="showPassword = !showPassword" aria-label="Mostrar contraseña"
                    tabindex="-1" class="text-text-muted hover:text-accent transition-colors">
                    <svg x-show="!showPassword" class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor"
                        stroke-width="1.5">
                        <path d="M1 10s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6z" />
                        <circle cx="10" cy="10" r="2.5" />
                    </svg>
                    <svg x-show="showPassword" x-cloak class="w-4 h-4" viewBox="0 0 20 20" fill="none"
                        stroke="currentColor" stroke-width="1.5">
                        <path
                            d="M3 3l14 14M10 4c4.5 0 7.7 4.1 8.5 5.5-.6 1-2.3 3.2-4.8 4.5M5 5.5C2.8 7 1.3 9.1 1 10c.8 1.4 4 6 9 6 1.7 0 3.2-.5 4.5-1.3" />
                        <path d="M7.5 7.7a2.5 2.5 0 003.8 3.2" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Submit -->
        <button type="submit" id="btnLogin" wire:loading.attr="disabled"
            class="w-full bg-accent hover:opacity-95 active:scale-[0.98] transition-all text-white font-semibold py-3 rounded-xl shadow-md cursor-pointer text-xs md:text-sm flex items-center justify-center gap-2">
            <span wire:loading.remove wire:target="login">Iniciar sesión</span>
            <span wire:loading wire:target="login">Procesando...</span>
        </button>
    </form>

    <div class="text-center mt-6">
        <span class="text-text-muted text-xs">© 2026 HRMedia · v1.0</span>
    </div>
</section>
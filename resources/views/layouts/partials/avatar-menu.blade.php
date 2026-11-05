<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <button
        type="button"
        @click="open = !open"
        class="flex items-center gap-2 pl-1 pr-2 py-1 rounded-lg hover:bg-surface-hover transition-colors cursor-pointer"
    >
        <div class="w-8 h-8 rounded-full bg-accent/15 text-accent flex items-center justify-center text-xs font-semibold shrink-0">
            {{ Auth::user()->initials() }}
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 text-text-muted transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-52 bg-background border border-border rounded-xl shadow-lg overflow-hidden z-50"
    >
        <div class="px-4 py-3 border-b border-border">
            <p class="text-sm font-medium text-text truncate">{{ Auth::user()->name }}</p>
            <p class="text-xs text-text-muted truncate">{{ Auth::user()->email }}</p>
        </div>

        <a
            href="{{ route('profile.edit') }}"
            class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-text-muted hover:bg-surface-hover hover:text-accent transition-colors cursor-pointer"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="7" r="4" />
                <path d="M5.5 21a6.5 6.5 0 0113 0" />
            </svg>
            <span>Mi perfil</span>
        </a>

        <form action="/logout" method="POST" class="border-t border-border">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-text-muted hover:bg-surface-hover hover:text-accent transition-colors cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 3h4a1 1 0 011 1v12a1 1 0 01-1 1h-4M8 14l4-4-4-4M12 10H3" />
                </svg>
                <span>Salir</span>
            </button>
        </form>
    </div>
</div>
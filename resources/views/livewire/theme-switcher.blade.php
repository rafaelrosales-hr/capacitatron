<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<div class="relative">
    <button
        id="theme-btn"
        type="button"
        class="w-9 h-9 flex items-center justify-center rounded-lg border border-border text-text-muted hover:bg-surface-hover hover:text-text transition-colors cursor-pointer"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/>
            <circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/>
            <circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/>
            <circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/>
            <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/>
        </svg>
    </button>

    <div
        id="theme-modal"
        class="hidden absolute right-0 mt-2 w-56 bg-background border border-border rounded-xl shadow-lg p-4 z-50 transition-colors"
    >
        <p class="text-xs font-semibold text-text-muted uppercase tracking-wide mb-3">Tema</p>
        <div class="flex gap-2 mb-4">
            <button type="button" class="theme-mode-btn flex-1 text-xs font-semibold py-1.5 rounded-lg border transition-colors cursor-pointer" data-mode="light">Claro</button>
            <button type="button" class="theme-mode-btn flex-1 text-xs font-semibold py-1.5 rounded-lg border transition-colors cursor-pointer" data-mode="dark">Oscuro</button>
        </div>

        <p class="text-xs font-semibold text-text-muted uppercase tracking-wide mb-3">Acento</p>
        <div class="flex justify-between gap-2">
            <button type="button" class="accent-btn w-6 h-6 rounded-full bg-orange-500 ring-2 ring-offset-2 ring-offset-background ring-transparent hover:ring-orange-300 transition-all cursor-pointer" data-accent="orange"></button>
            <button type="button" class="accent-btn w-6 h-6 rounded-full bg-blue-500 ring-2 ring-offset-2 ring-offset-background ring-transparent hover:ring-blue-300 transition-all cursor-pointer" data-accent="blue"></button>
            <button type="button" class="accent-btn w-6 h-6 rounded-full bg-purple-500 ring-2 ring-offset-2 ring-offset-background ring-transparent hover:ring-purple-300 transition-all cursor-pointer" data-accent="purple"></button>
            <button type="button" class="accent-btn w-6 h-6 rounded-full bg-pink-500 ring-2 ring-offset-2 ring-offset-background ring-transparent hover:ring-pink-300 transition-all cursor-pointer" data-accent="rose"></button>
        </div>
    </div>

    @script
    <script>
        const ACCENT_RINGS = {
            orange: 'ring-orange-500',
            blue: 'ring-blue-500',
            purple: 'ring-purple-500',
            rose: 'ring-rose-500',
        };

        function initThemeWidget(el) {
            const btn = el.querySelector('#theme-btn');
            const modal = el.querySelector('#theme-modal');

            btn?.addEventListener('click', (e) => {
                e.stopPropagation();
                const isHidden = modal?.classList.contains('hidden');
                document.dispatchEvent(new CustomEvent('dropdown:close-all', { detail: { except: modal } }));
                if (isHidden) modal?.classList.remove('hidden');
            });

            document.addEventListener('dropdown:close-all', (e) => {
                if (e.detail?.except !== modal) modal?.classList.add('hidden');
            });

            document.addEventListener('click', () => modal?.classList.add('hidden'));
            modal?.addEventListener('click', (e) => e.stopPropagation());

            // --- Modo claro/oscuro ---
            function setActiveModeBtn(mode) {
                el.querySelectorAll('.theme-mode-btn').forEach((b) => {
                    const isActive = b.dataset.mode === mode;
                    b.classList.toggle('border-accent', isActive);
                    b.classList.toggle('bg-accent/15', isActive);
                    b.classList.toggle('text-accent', isActive);
                    b.classList.toggle('border-border', !isActive);
                    b.classList.toggle('text-text-muted', !isActive);
                    b.classList.toggle('hover:bg-surface-hover', !isActive);
                });
            }
            setActiveModeBtn(localStorage.getItem('theme') || 'light');

            el.querySelectorAll('.theme-mode-btn').forEach((modeBtn) => {
                modeBtn.addEventListener('click', () => {
                    const mode = modeBtn.dataset.mode;
                    setActiveModeBtn(mode);
                    document.documentElement.classList.toggle('dark', mode === 'dark');
                    localStorage.setItem('theme', mode);
                    window.dispatchEvent(new CustomEvent('theme:changed'));
                });
            });

            // --- Acento ---
            function setActiveAccentBtn(accent) {
                el.querySelectorAll('.accent-btn').forEach((b) => {
                    b.classList.remove(...Object.values(ACCENT_RINGS));
                    b.classList.add('ring-transparent');
                });
                const activeBtn = el.querySelector(`.accent-btn[data-accent="${accent}"]`);
                activeBtn?.classList.remove('ring-transparent');
                activeBtn?.classList.add(ACCENT_RINGS[accent]);
                setActiveModeBtn(localStorage.getItem('theme') || 'light');
            }
            setActiveAccentBtn(localStorage.getItem('accent') || 'orange');

            el.querySelectorAll('.accent-btn').forEach((accentBtn) => {
                accentBtn.addEventListener('click', () => {
                    const accent = accentBtn.dataset.accent;
                    document.documentElement.dataset.accent = accent;
                    localStorage.setItem('accent', accent);
                    window.dispatchEvent(new CustomEvent('theme:changed'));
                    setActiveAccentBtn(accent);
                });
            });
        }

        initThemeWidget($wire.$el);
    </script>
    @endscript
</div>
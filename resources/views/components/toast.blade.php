<div
    x-data="{
        show: false,
        title: '',
        message: '',
        timeoutId: null,
        open(detail) {
            this.title = detail.title ?? '¡Operación exitosa!';
            this.message = detail.message ?? '';
            clearTimeout(this.timeoutId);
            this.show = true;
            this.timeoutId = setTimeout(() => this.show = false, 4000);
        },
        close() {
            clearTimeout(this.timeoutId);
            this.show = false;
        }
    }"
    x-on:toast.window="open($event.detail)"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed top-20 right-6 z-[100] flex items-center gap-3 px-4 py-3 rounded-2xl border border-border shadow-2xl backdrop-blur-xl bg-background/10 text-text max-w-sm"
    role="alert"
>
    <div class="w-1.5 h-8 rounded-full bg-accent shrink-0"></div>
    <div class="flex-1 pr-2">
        <p class="text-sm font-semibold" x-text="title"></p>
        <p class="text-xs text-text-muted" x-text="message"></p>
    </div>
    <button
        type="button"
        @click="close()"
        aria-label="Cerrar notificación"
        class="p-1 rounded-lg text-text-muted hover:text-text hover:bg-surface-hover transition-colors cursor-pointer"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
    </button>
</div>
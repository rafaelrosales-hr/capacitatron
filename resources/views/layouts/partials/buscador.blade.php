<div class="relative flex-1 max-w-4xl mr-4" x-data="{ query: '' }">
    <svg
        xmlns="http://www.w3.org/2000/svg"
        class="w-4 h-4 text-text-muted absolute left-3 top-1/2 -translate-y-1/2 transition-colors"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
    >
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input
        type="text"
        x-model="query"
        placeholder="Buscar..."
        class="w-full bg-surface-hover/50 border border-border rounded-lg pl-9 pr-3 py-2 text-sm text-text placeholder-text-muted outline-none focus:border-accent focus:bg-background transition-colors"
    />
</div>
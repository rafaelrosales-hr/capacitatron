<nav class="w-full h-16 border-b border-border bg-background text-text flex items-center justify-end px-6 font-montserrat transition-colors">
   {{--  @include('layouts.partials.buscador') --}}

    <div class="flex items-center gap-3">
        <livewire:theme-switcher />
        <div class="w-px h-6 bg-border"></div>
        @include('layouts.partials.avatar-menu')
    </div>
</nav>
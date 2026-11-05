<x-app-layout>
    <div
        class="bg-transparent border border-border shadow-lg px-6 py-5 rounded-xl flex flex-col w-full mt-4">
        <h2 class="text-accent font-syne font-semibold text-xl text-center">
            Carga de Catálogos
        </h2>
    </div>
    <div wire:key="component-subject-area">
        <livewire:components.subjet-area />
    </div>

    <div wire:key="component-occupations">
        <livewire:components.ocupations />
    </div>
</x-app-layout>

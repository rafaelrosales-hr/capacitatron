<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UserImport;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $file;

    public function downloadTemplate()
    {
        return Storage::disk('public')->download('plantillas/Plantilla-Colaboradores.xlsx');
    }

    public function import()
    {
        $this->validate([
            'file' => 'required|mimes:csv,xls,xlsx|max:10240',
        ]);

        try {
            Excel::import(new UserImport(), $this->file->getRealPath());

            $this->reset('file');

            $this->dispatch('toast', title: 'Importación exitosa', message: 'Los usuarios se importaron correctamente.');
        } catch (\Exception $e) {
            $this->dispatch('toast', title: 'Error al importar', message: 'Hubo un error al procesar el archivo: ' . $e->getMessage());
        }
    }
}; ?>

<div class="flex flex-col gap-4">
    <section
        class="rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm px-6 py-4 flex justify-between items-center">
        <div>
            <h2 class="text-text font-bold text-2xl font-syne text-center">Carga de Colaboradores</h2>
        </div>
    </section>

    <section
        class="rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm px-6 py-5 flex flex-col w-full">

        <div class="mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="text-text font-semibold font-syne text-xl">
                Carga de catálogo de Colaboradores
            </h2>
            <button
                type="button"
                wire:click="downloadTemplate"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-accent bg-accent/10 border border-accent rounded-xl hover:bg-accent/20 transition-colors disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer">
                <span>Descargar Plantilla</span>
            </button>
        </div>

        <form wire:submit.prevent="import" class="w-full space-y-4">
            <div class="w-full">
                <label for="file-users"
                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-2xl cursor-pointer bg-surface-hover/20 border-border backdrop-blur-md hover:bg-surface-hover/40 hover:border-accent/50 transition-all duration-300 shadow-sm group">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">
                        @if ($file)
                        <div class="p-3 bg-emerald-500/10 rounded-full text-emerald-500 mb-4 animate-bounce">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <p class="mb-1 text-sm text-emerald-500 font-medium">¡Archivo cargado listo para procesar!
                        </p>
                        <p class="text-xs text-text-muted italic">{{ $file->getClientOriginalName() }}</p>
                        @else
                        <div
                            class="p-3 bg-accent/10 rounded-full text-accent group-hover:scale-110 group-hover:bg-accent/20 transition-all duration-300 mb-4">
                            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 20 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                            </svg>
                        </div>
                        <p class="mb-2 text-sm text-text"><span class="font-semibold">Haz clic para cargar</span> o
                            arrastra y suelta</p>
                        <p class="text-xs text-text-muted mb-1">Archivos permitidos: <span
                                class="text-accent font-medium">CSV, XLS o XLSX</span></p>
                        @endif

                        <div wire:loading wire:target="file" class="mt-2 text-xs text-accent animate-pulse">
                            Subiendo archivo temporalmente...
                        </div>
                    </div>

                    <input id="file-users" type="file" wire:model="file" class="hidden" accept=".csv, .xls, .xlsx" />
                </label>

                @error('file')
                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            @if ($file)
            <div class="flex justify-end mt-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="import"
                    class="min-w-[180px] flex items-center justify-center gap-2 px-5 py-2.5 bg-accent hover:opacity-90 active:scale-[0.98] text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-md disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer whitespace-nowrap">
                    <span wire:loading.remove wire:target="import">Procesar e Importar</span>
                    <span wire:loading wire:target="import" class="flex items-center gap-2">
                        Procesando usuarios...
                    </span>
                </button>
            </div>
            @endif
        </form>
    </section>
</div>
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Imports\CoursesImport;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $file;

    /**
     * Valida el archivo en tiempo real cuando el usuario lo selecciona.
     * Esto asegura que sea un formato correcto antes de habilitar el botón de procesar.
     */
    public function updatedFile()
    {
        $this->validate([
            'file' => 'required|mimes:csv,xls,xlsx|max:10240', // Máximo 10MB
        ]);
    }

    /**
     * Descarga la plantilla de Excel para la carga masiva de cursos.
     */
    public function downloadTemplate()
    {
        $path = 'plantillas/Plantilla-Cursos.xlsx';

        if (!Storage::disk('public')->exists($path)) {
            $this->dispatch(
                'toast',
                title: 'Archivo no encontrado',
                message: 'La plantilla no está disponible en el servidor.'
            );
            return;
        }

        return Storage::disk('public')->download($path, 'Plantilla-Cursos.xlsx');
    }

    /**
     * Procesa la importación real al presionar el botón de enviar formulario.
     */
    public function import()
    {
        if (!$this->file) {
            return;
        }

        try {
            $import = new CoursesImport();
            Excel::import($import, $this->file->getRealPath());

            $this->reset('file');

            if (count($import->skippedDuplicates) > 0) {
                $duplicatesList = implode(', ', array_unique($import->skippedDuplicates));

                $this->dispatch(
                    'toast',
                    title: 'Importación parcial',
                    message: "Se importaron los cursos nuevos. Se omitieron por estar repetidos."
                );
            } else {
                $this->dispatch(
                    'toast',
                    title: 'Importación exitosa',
                    message: '¡Cursos importados exitosamente!'
                );
            }
        } catch (\Exception $e) {
            $this->dispatch(
                'toast',
                title: 'Error al importar',
                message: 'Hubo un error al procesar el archivo: ' . $e->getMessage()
            );
        }
    }
}; ?>

<section class="rounded-2xl border border-border bg-transparent shadow-sm px-6 py-5 flex flex-col w-full mt-4">

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-accent font-bold text-xl font-syne">
            Carga masiva de Cursos
        </h2>

        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="downloadTemplate"
                wire:loading.attr="disabled"
                wire:target="downloadTemplate"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-accent bg-accent/10 border border-accent rounded-xl hover:bg-accent/20 transition-colors disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Descargar plantilla
            </button>

            <a
                href="{{ route('courses-manager') }}"
                wire:navigate
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-accent bg-accent/10 border border-accent rounded-xl hover:bg-accent/20 transition-colors"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>
                Cursos
            </a>
        </div>
    </div>

    <form wire:submit.prevent="import" class="w-full space-y-4">
        <div class="w-full">
            <label
                for="file-subject"
                class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed rounded-2xl cursor-pointer bg-surface-hover/20 border-border backdrop-blur-md hover:bg-surface-hover/40 hover:border-accent/50 transition-all duration-300 shadow-sm group">
                <div class="flex flex-col items-center justify-center pt-5 pb-6 px-4 text-center">

                    @if ($file)
                    <div class="p-3 bg-emerald-500/10 rounded-full text-emerald-500 mb-4 animate-bounce">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <p class="mb-1 text-sm text-emerald-500 font-medium">¡Archivo cargado listo para procesar!</p>
                    <p class="text-xs text-text-muted italic">{{ $file->getClientOriginalName() }}</p>
                    @else
                    <div class="p-3 bg-accent/10 rounded-full text-accent group-hover:scale-110 group-hover:bg-accent/20 transition-all duration-300 mb-4">
                        <svg class="w-8 h-8" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                        </svg>
                    </div>
                    <p class="mb-2 text-sm text-text"><span class="font-semibold">Haz clic para cargar</span> o arrastra y suelta</p>
                    <p class="text-xs text-text-muted mb-1">Archivos permitidos: <span class="text-accent font-medium">CSV, XLS o XLSX</span></p>
                    @endif

                    <div wire:loading wire:target="file" class="mt-2 text-xs text-accent animate-pulse">
                        Subiendo archivo temporalmente...
                    </div>
                </div>

                <input
                    id="file-subject"
                    type="file"
                    wire:model="file"
                    class="hidden"
                    accept=".csv, .xls, .xlsx, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
            </label>

            @error('file')
            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        @if ($file)
        <div class="flex justify-end mt-2">
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="import"
                class="min-w-[200px] flex items-center justify-center gap-2 px-5 py-2.5 bg-accent hover:opacity-90 active:scale-[0.98] text-white text-sm font-semibold rounded-xl transition-all duration-300 shadow-md disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer whitespace-nowrap">
                <span wire:loading.remove wire:target="import">Procesar e Importar</span>
                <span wire:loading.flex wire:target="import" class="items-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white shrink-0" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Guardando en Base de Datos...
                </span>
            </button>
        </div>
        @endif
    </form>
</section>
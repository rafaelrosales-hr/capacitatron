<?php

use App\Models\User;
use App\Exports\UserCoursesSummaryExport;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Maatwebsite\Excel\Facades\Excel;

new #[Layout('layouts.app')] class extends Component {
    public $selectedUserId = '';
    public $users;

    public function mount()
    {
        $this->users = User::orderBy('name')->get();
    }

    public function exportSummary()
    {
        return Excel::download(new UserCoursesSummaryExport($this->selectedUserId), 'cursos-usuario-' . ($this->selectedUserId ?: 'todos') . '.xlsx');
    }

    public function exportAllSummary()
    {
        return Excel::download(new UserCoursesSummaryExport(''), 'cursos-todos-los-usuarios.xlsx');
    }

    public function exportPdf()
    {
        if (!$this->selectedUserId) {
            $this->dispatch(
                'toast',
                title: 'Selecciona un usuario',
                message: 'Por favor selecciona un usuario para generar el PDF.'
            );
            return;
        }

        return redirect()->route('pdf.user.courses', $this->selectedUserId);
    }
};

?>

<div class="space-y-6">
    <section class="rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-2xl font-bold text-text font-syne">Reporte consolidado por colaborador</h1>
                <p class="mt-2 text-sm text-text-muted">Selecciona un usuario para filtrar sus cursos y exportar.</p>

                <!-- Selector de Usuario -->
                <div class="mt-4">
                    <select
                        wire:model.live="selectedUserId"
                        class="w-full md:w-64 px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                        <option value="">-- Todos los usuarios --</option>
                        @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} {{ $user->surname }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                {{-- <button
                    type="button"
                    wire:click="exportPdf"
                    @if (!$selectedUserId) disabled @endif
                    class="inline-flex items-center justify-center rounded-xl bg-accent hover:opacity-90 active:scale-[0.98] px-5 py-3 text-sm font-semibold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:opacity-50 cursor-pointer">
                    Descargar PDF
                </button> --}}
                <button
                    type="button"
                    wire:click="exportSummary"
                    @if (!$selectedUserId) disabled @endif
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] px-5 py-3 text-sm font-semibold text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-emerald-600 cursor-pointer">
                    Descargar Excel
                </button>
            </div>
        </div>
    </section>

    <!-- Card: Resumen de todos los usuarios -->
    <section class="rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-6">
        <div class="flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h2 class="text-lg font-bold text-text font-syne">Reporte general de todos los colaboradores</h2>
                <p class="mt-2 text-sm text-text-muted">Descarga un Excel con los cursos de todos los usuarios registrados, sin necesidad de seleccionar uno.</p>
            </div>

            <div class="flex flex-wrap gap-2 shrink-0">
                <button
                    type="button"
                    wire:click="exportAllSummary"
                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] px-5 py-3 text-sm font-semibold text-white transition-all cursor-pointer">
                    Descargar Excel de Todos
                </button>
            </div>
        </div>
    </section>
</div>
<?php

use App\Exports\UserCoursesSummaryExport;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Maatwebsite\Excel\Facades\Excel;

new #[Layout('layouts.app')] class extends Component
{
    public function exportSummary(): \Symfony\Component\HttpFoundation\Response
    {
        return Excel::download(new UserCoursesSummaryExport(), 'cursos-por-usuario.xlsx');
    }
}; ?>

<div
    class="space-y-6 bg-white/10 backdrop-blur-md border border-white/20 shadow-lg px-6 py-4 rounded-xl flex justify-between items-center">
      <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
            <div class="flex gap-4 md:flex-row items-center justify-between">
                  <div>
                        <h1 class="mt-1 text-2xl font-bold text-slate-100" style="font-family: 'Syne', sans-serif;">Reporte consolidado por usuario</h1>
                        <p class="mt-2 text-sm text-slate-400">Descarga un Excel agrupado por usuario con los cursos en los que asistió.</p>
                      </div>

                  <button
                        type="button"
                        wire:click="exportSummary"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-500 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400"
                        style="font-family: 'Syne', sans-serif;"
                     >
                        Descargar Excel consolidado
                      </button>
                </div>
          </section>
</div>
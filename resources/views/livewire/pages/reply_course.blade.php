<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\AdditionalCourse;

new #[Layout('layouts.app')] class extends Component {
    public array $requests = [];

    public function mount(): void
    {
        $this->loadRequests();
    }

    public function loadRequests(): void
    {
        // Cargamos la relación 'user' para obtener el nombre del usuario
        $this->requests = AdditionalCourse::with('user')->latest()->get()->toArray();
    }

    public function updateStatus(int $requestId, string $status): void
    {
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $this->dispatch('toast', title: 'Error', message: 'Estado no válido.');
            return;
        }

        $request = AdditionalCourse::findOrFail($requestId);
        $request->update(['status' => $status]);

        $this->loadRequests();

        $statusLabel = match ($status) {
            'approved' => 'aprobada',
            'rejected' => 'rechazada',
            default => 'actualizada',
        };

        $this->dispatch(
            'toast',
            title: 'Solicitud ' . $statusLabel,
            message: 'El estado se actualizó correctamente.'
        );
    }
}; ?>

<div class="flex flex-col gap-6 min-h-screen p-6 font-montserrat w-full">

    <!-- Cabecera -->
    <div class="rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-6">
        <h1 class="text-2xl font-bold text-accent font-syne">Revisión de solicitudes</h1>
        <p class="mt-1 text-sm text-text-muted">Aprueba o rechaza las solicitudes de cursos adicionales.</p>
    </div>

    <!-- Tabla -->
    <div class="w-full overflow-x-auto rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm">
        <table class="w-full border-collapse text-left text-sm text-text-muted">
            <thead class="border-b border-border bg-surface-hover/30 text-xs font-semibold uppercase tracking-wide text-text-muted">
                <tr>
                    <th class="px-6 py-4">Colaborador</th>
                    <th class="px-6 py-4">Curso</th>
                    <th class="px-6 py-4">Institución</th>
                    <th class="px-6 py-4">Horas</th>
                    <th class="px-6 py-4">Estado</th>
                    <th class="px-6 py-4 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/40">
                @forelse($requests as $request)
                    @php
                        $statusClasses = match ($request['status'] ?? 'pending') {
                            'approved' => 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
                            'rejected' => 'bg-rose-500/10 text-rose-500 border border-rose-500/20',
                            default => 'bg-amber-500/10 text-amber-500 border border-amber-500/20',
                        };

                        $userName = isset($request['user'])
                            ? trim(
                                ($request['user']['name'] ?? '') .
                                    ' ' .
                                    ($request['user']['surname'] ?? '') .
                                    ' ' .
                                    ($request['user']['second_surname'] ?? ''),
                            )
                            : 'N/A';
                    @endphp

                    <tr wire:key="request-{{ $request['id'] }}" class="hover:bg-surface-hover/40 transition-colors">
                        <td class="px-6 py-4 font-medium text-text">
                            {{ $userName !== '' ? $userName : 'Usuario desconocido' }}
                        </td>
                        <td class="px-6 py-4 text-text">{{ $request['name'] }}</td>
                        <td class="px-6 py-4 text-text-muted">{{ $request['institution'] }}</td>
                        <td class="px-6 py-4 text-text-muted">{{ $request['duration'] }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block rounded-full px-3 py-1 text-xs font-medium {{ $statusClasses }}">
                                {{ ucfirst($request['status'] ?? 'pending') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    type="button"
                                    wire:click="updateStatus({{ $request['id'] }}, 'approved')"
                                    class="rounded-lg bg-emerald-600 hover:bg-emerald-700 px-3 py-1.5 text-xs font-medium text-white transition-colors cursor-pointer"
                                >
                                    Aprobar
                                </button>
                                <button
                                    type="button"
                                    wire:click="updateStatus({{ $request['id'] }}, 'rejected')"
                                    class="rounded-lg bg-rose-600 hover:bg-rose-700 px-3 py-1.5 text-xs font-medium text-white transition-colors cursor-pointer"
                                >
                                    Rechazar
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-text-muted">
                            No hay solicitudes registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
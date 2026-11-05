<?php

use App\Exports\AttendanceExport;
use App\Models\CourseHasUser;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

new class extends Component {
    use WithPagination;

    public int $courseId = 0;
    public string $search = '';

    // Filtros y ordenamiento
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public string $filterArea = '';
    public string $filterAttendance = '';

    public array $attendance = [];

    public function mount(int $courseId = 0): void
    {
        $this->courseId = $courseId;
        $this->loadAttendance();
    }

    public function updatedCourseId(int $courseId): void
    {
        $this->courseId = $courseId;
        $this->attendance = [];
        $this->reset(['search', 'filterArea', 'filterAttendance']);
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
        $this->resetPage();
        $this->loadAttendance();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterArea(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAttendance(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (!in_array($field, ['name', 'area', 'attendance'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'filterArea', 'filterAttendance']);
        $this->resetPage();
    }

    public function loadAttendance(): void
    {
        if ($this->courseId <= 0) {
            $this->attendance = [];

            return;
        }

        $this->attendance = CourseHasUser::query()
            ->where('course_id', $this->courseId)
            ->pluck('attended_rh', 'user_id')
            ->map(fn($value) => (bool) $value)
            ->toArray();
    }

    /**
     * Guarda la asistencia de un usuario.
     * Si $attended viene explícito (desde el checkbox vía wire:change),
     * se usa ese valor directo en lugar de esperar la sincronización del binding.
     */
    public function saveAttendance(int $userId, ?bool $attended = null): void
    {
        if ($this->courseId <= 0) {
            return;
        }

        if ($attended !== null) {
            $this->attendance[$userId] = $attended;
        } else {
            $attended = (bool) ($this->attendance[$userId] ?? false);
        }

        CourseHasUser::query()->updateOrCreate(
            [
                'course_id' => $this->courseId,
                'user_id' => $userId,
            ],
            [
                'attended_rh' => $attended,
            ],
        );

        $user = User::find($userId);
        $userName = $user ? trim($user->name . ' ' . ($user->surname ?? '')) : 'Usuario';

        $this->dispatch(
            'toast',
            title: $attended ? 'Asistencia guardada' : 'Asistencia removida',
            message: "\"{$userName}\" se marcó como " . ($attended ? 'presente.' : 'no presente.')
        );
    }

    public function exportAttendance()
    {
        if ($this->courseId <= 0) {
            return;
        }

        return Excel::download(new AttendanceExport($this->courseId), 'asistencia-curso-' . $this->courseId . '.xlsx');
    }

    public function getAreasProperty()
    {
        return User::query()
            ->whereNotNull('area')
            ->where('area', '!=', '')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');
    }

    public function getUsersProperty()
    {
        $pivotTable = (new CourseHasUser())->getTable();

        // Subconsulta: ids de usuarios que asistieron a este curso
        $attendedIds = CourseHasUser::query()
            ->where('course_id', $this->courseId)
            ->where('attended_rh', true)
            ->select('user_id');

        return User::query()
            ->when(trim($this->search) !== '', function ($query) {
                $term = '%' . trim($this->search) . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'like', $term)
                        ->orWhere('surname', 'like', $term)
                        ->orWhere('second_surname', 'like', $term)
                        ->orWhere('area', 'like', $term);
                });
            })
            ->when($this->filterArea !== '', fn($q) => $q->where('area', $this->filterArea))
            ->when($this->filterAttendance === '1', fn($q) => $q->whereIn('id', $attendedIds))
            ->when($this->filterAttendance === '0', fn($q) => $q->whereNotIn('id', $attendedIds))
            ->when(
                $this->sortField === 'attendance',
                fn($q) => $q->orderBy(
                    CourseHasUser::query()
                        ->select('attended_rh')
                        ->whereColumn("{$pivotTable}.user_id", 'users.id')
                        ->where('course_id', $this->courseId)
                        ->limit(1),
                    $this->sortDirection
                ),
                fn($q) => $q->orderBy($this->sortField, $this->sortDirection)
            )
            ->orderBy('name')
            ->paginate(8);
    }
}; ?>

<div class="w-full">
    @if ($courseId <= 0)
        <div class="rounded-2xl border border-dashed border-border bg-background/40 backdrop-blur-md p-10 text-center text-sm text-text-muted">
        Selecciona un curso para ver la lista de asistencia.
</div>
@else
<section class="space-y-3">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-border/60">
        <div class="px-4">
            <span class="text-[10px] font-bold uppercase tracking-wider text-accent font-syne">Curso seleccionado</span>
            <h2 class="text-2xl font-semibold text-text font-syne">Lista de asistencia</h2>
        </div>

        <div class="flex flex-wrap items-center gap-3 px-4">
            <!-- Buscador -->
            <div class="relative w-full sm:w-64">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar colaborador..."
                    class="w-full bg-background/50 backdrop-blur-md border border-border rounded-xl pl-9 pr-3 py-1.5 text-xs text-black placeholder-text-muted outline-none focus:border-accent focus:bg-background/80 transition-all shadow-sm" />
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-text-muted absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </div>

            <!-- Botón: limpiar filtros -->
            @if ($search !== '' || $filterArea !== '' || $filterAttendance !== '')
            <button
                type="button"
                wire:click="clearFilters"
                class="rounded-xl border border-border px-3 py-1.5 text-xs text-text-muted hover:bg-surface-hover/40 transition-all cursor-pointer">
                Limpiar filtros
            </button>
            @endif

            <!-- Botón: exportar -->
            <button
                type="button"
                wire:click="exportAttendance"
                class="inline-flex items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 px-3 py-1.5 text-xs font-semibold hover:bg-emerald-500/20 transition-all shadow-sm cursor-pointer">
                Descargar Excel
            </button>
        </div>
    </div>

    <div class="overflow-x-auto bg-transparent backdrop-blur-md shadow-sm">
        <table class="w-full text-left text-xs">
            <thead class="bg-surface-hover/30 border-b border-border text-text-muted uppercase text-[10px] font-bold tracking-wider">
                <tr>
                    {{-- Usuario --}}
                    <th class="px-5 py-3.5 align-top">
                        <button type="button" wire:click="sortBy('name')" class="inline-flex items-center gap-1 uppercase hover:text-text cursor-pointer">
                            Usuario
                            @if ($sortField === 'name')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                    </th>

                    {{-- Área --}}
                    <th class="px-5 py-3.5 align-top">
                        <button type="button" wire:click="sortBy('area')" class="inline-flex items-center gap-1 uppercase hover:text-text cursor-pointer">
                            Area
                            @if ($sortField === 'area')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                        <select
                            wire:model.live="filterArea"
                            class="mt-1.5 block w-full normal-case font-normal bg-background/50 border border-border rounded-lg px-2 py-1 text-[11px] text-black outline-none focus:border-accent">
                            <option value="">Todas</option>
                            @foreach ($this->areas as $area)
                            <option value="{{ $area }}">{{ $area }}</option>
                            @endforeach
                        </select>
                    </th>

                    {{-- Asistió --}}
                    <th class="px-5 py-3.5 text-center w-36 align-top">
                        <button type="button" wire:click="sortBy('attendance')" class="inline-flex items-center gap-1 uppercase hover:text-text cursor-pointer">
                            Asistió
                            @if ($sortField === 'attendance')
                            <span>{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </button>
                        <select
                            wire:model.live="filterAttendance"
                            class="mt-1.5 block w-full normal-case font-normal bg-background/50 border border-border rounded-lg px-2 py-1 text-[11px] text-black outline-none focus:border-accent">
                            <option value="">Todos</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y divide-border">
                @forelse ($this->users as $user)
                <tr wire:key="user-{{ $user->id }}" class="hover:bg-surface-hover/40 transition-colors">
                    <td class="px-5 py-3 font-medium text-text">
                        {{ $user->name }} {{ $user->surname ?? '' }} {{ $user->second_surname ?? '' }}
                    </td>
                    <td class="px-5 py-3 text-text-muted">{{ $user->area }}</td>
                    <td class="px-5 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <label class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-[10px] font-medium cursor-pointer transition-colors {{ ($attendance[$user->id] ?? false) ? 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20' : 'bg-surface-hover/50 text-text-muted border-border' }}">
                                <input
                                    type="checkbox"
                                    wire:model="attendance.{{ $user->id }}"
                                    wire:change="saveAttendance({{ $user->id }}, $event.target.checked)"
                                    wire:loading.attr="disabled"
                                    wire:target="saveAttendance"
                                    class="w-3.5 h-3.5 rounded border-border bg-background text-accent focus:ring-accent focus:ring-offset-background cursor-pointer">
                                <span class="inline-flex items-center gap-1.5">
                                    @if ($attendance[$user->id] ?? false)
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Sí
                                    @else
                                    No
                                    @endif
                                </span>
                            </label>

                            <!-- Indicador de guardado en curso -->
                            <span wire:loading wire:target="saveAttendance" class="text-[10px] text-accent animate-pulse">
                                ...
                            </span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-5 py-8 text-center text-text-muted">
                        @if ($search !== '' || $filterArea !== '' || $filterAttendance !== '')
                        No se encontraron colaboradores con los filtros aplicados
                        @else
                        No hay colaboradores disponibles
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="py-3 px-4">
        {{ $this->users->links() }}
    </div>
</section>
@endif
</div>
<?php

use App\Models\Course;
use App\Models\CourseHasUser;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public int $selectedCourseId = 0;

    public function getCoursesProperty()
    {
        return Course::query()->orderBy('name')->get(['id', 'name']);
    }

    public function updatedSelectedCourseId(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        //
    }

    /**
     * Estadísticas calculadas con queries agregadas (no cargan todos los usuarios a PHP),
     * así que escalan bien sin importar cuántos usuarios tenga el sistema.
     */
    public function getStatsProperty()
    {
        $stats = [
            'total' => 0,
            'admin_attendance' => 0,
            'user_marked_taken' => 0,
            'both_match' => 0,
            'mismatch' => 0,
        ];

        if ($this->selectedCourseId <= 0) {
            return $stats;
        }

        $stats['total'] = User::count();

        $records = CourseHasUser::where('course_id', $this->selectedCourseId)->get(['user_id', 'attended', 'attended_rh', 'completed_at']);

        foreach ($records as $record) {
            $admin = (bool) $record->attended_rh === true;
            $marked = (bool) $record->attended === true || $record->completed_at !== null;

            if ($admin) {
                $stats['admin_attendance']++;
            }
            if ($marked) {
                $stats['user_marked_taken']++;
            }
            if ($admin && $marked) {
                $stats['both_match']++;
            }
            if ($admin !== $marked) {
                $stats['mismatch']++;
            }
        }
        return $stats;
    }

    /**
     * Tabla paginada: solo trae 15 usuarios por página, con su registro de asistencia
     * del curso seleccionado (si existe) vía left join, en una sola query.
     */
    public function getAttendanceReportProperty()
    {
        if ($this->selectedCourseId <= 0) {
            return null;
        }

        return User::query()
            ->select([
                'users.id as user_id',
                'users.name as user_name',
                'users.surname as user_surname',
                'users.email as user_email',
                'courses_has_users.attended_rh',
                'courses_has_users.attended',
                'courses_has_users.completed_at',
            ])
            ->leftJoin('courses_has_users', function ($join) {
                $join->on('courses_has_users.user_id', '=', 'users.id')
                    ->where('courses_has_users.course_id', '=', $this->selectedCourseId);
            })
            ->orderBy('users.name')
            ->paginate(8);
    }
}; ?>

<div class="flex flex-col gap-6 min-h-screen p-6 font-montserrat w-full">

    <!-- Header / Selector de Curso -->
    <section class="rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-6">
        <div class="flex flex-col gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-accent font-syne">
                    Reporte de Asistencia
                </p>
                <h1 class="mt-1 text-2xl font-bold text-text font-syne">
                    Asistencia por Curso
                </h1>
            </div>

            <div class="w-full">
                <label for="selected-course" class="block text-xs font-medium text-text-muted mb-1">
                    Selecciona un curso
                </label>

                <select
                    id="selected-course"
                    wire:model.live="selectedCourseId"
                    class="w-full px-4 py-3 bg-background  border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent text-sm transition-all">
                    <option value="0">-- Elige un curso --</option>

                    @foreach ($this->courses as $course)
                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    @if ($selectedCourseId > 0)
    <!-- Tarjetas de Estadísticas -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">

        <div class="rounded-2xl border border-border bg-background/40 backdrop-blur-md p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-text-muted">
                Total usuarios
            </p>
            <p class="mt-2 text-3xl font-bold text-text">
                {{ $this->stats['total'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-border bg-background/40 backdrop-blur-md p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-sky-500">
                Asistencia Recursos Humanos
            </p>
            <p class="mt-2 text-3xl font-bold text-sky-500">
                {{ $this->stats['admin_attendance'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-border bg-background/40 backdrop-blur-md p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-500">
                Marcados por usuario
            </p>
            <p class="mt-2 text-3xl font-bold text-blue-500">
                {{ $this->stats['user_marked_taken'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-border bg-background/40 backdrop-blur-md p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-purple-500">
                Coinciden
            </p>
            <p class="mt-2 text-3xl font-bold text-purple-500">
                {{ $this->stats['both_match'] }}
            </p>
        </div>

        <div class="rounded-2xl border border-border bg-background/40 backdrop-blur-md p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-amber-500">
                Discrepancia
            </p>
            <p class="mt-2 text-3xl font-bold text-amber-500">
                {{ $this->stats['mismatch'] }}
            </p>
        </div>
    </div>

    <!-- Tabla de Asistencia -->
    <div class="rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-6">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-accent font-syne">
                Detalle de asistencia
            </p>
            <h2 class="mt-1 text-lg font-semibold text-text font-syne">
                Usuarios del curso
            </h2>
        </div>

        @if ($this->attendanceReport->isNotEmpty())
        <div class="w-full overflow-x-auto rounded-2xl border border-border bg-background/30">
            <table class="w-full text-left border-collapse text-sm text-text-muted">
                <thead class="bg-surface-hover/30 text-xs font-semibold uppercase tracking-[0.24em] text-text-muted border-b border-border">
                    <tr>
                        <th class="px-6 py-4">Usuario</th>
                        <th class="px-6 py-4">Correo</th>
                        <th class="px-6 py-4 text-center">Asistencia (Pase de Lista)</th>
                        <th class="px-6 py-4 text-center">Marcado por Usuario (Mis Cursos)</th>
                        <th class="px-6 py-4 text-center">Fecha de asistencia</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">
                    @foreach ($this->attendanceReport as $user)
                    @php
                    $adminAttendance = (bool) $user->attended_rh === true;
                    $userMarkedAsTaken = (bool) $user->attended === true || $user->completed_at !== null;
                    @endphp
                    <tr wire:key="user-{{ $user->user_id }}" class="hover:bg-surface-hover/40 transition-colors">
                        <td class="px-6 py-4 font-medium text-text">
                            {{ $user->user_name }} {{ $user->user_surname }}
                        </td>
                        <td class="px-6 py-4 text-text-muted">{{ $user->user_email }}</td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center">
                                @if ($adminAttendance)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-500/10 px-3 py-1 text-xs font-semibold text-sky-500 border border-sky-500/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                    Asistió
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-hover/50 px-3 py-1 text-xs font-semibold text-text-muted border border-border">
                                    <span class="h-1.5 w-1.5 rounded-full bg-text-muted"></span>
                                    No asistió
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center">
                                @if ($userMarkedAsTaken)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-3 py-1 text-xs font-semibold text-blue-500 border border-blue-500/20">
                                    <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                                    Tomado
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-hover/50 px-3 py-1 text-xs font-semibold text-text-muted border border-border">
                                    <span class="h-1.5 w-1.5 rounded-full bg-text-muted"></span>
                                    No tomado
                                </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center text-text-muted">
                            {{ $user->completed_at ? \Carbon\Carbon::parse($user->completed_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $this->attendanceReport->links() }}
        </div>
        @else
        <div class="rounded-2xl border border-dashed border-border/60 bg-surface-hover/20 p-8 text-center text-sm text-text-muted">
            No hay usuarios asignados a este curso.
        </div>
        @endif
    </div>
    @else
    <div class="rounded-2xl border border-dashed border-border/60 bg-background/40 p-10 text-center text-sm text-text-muted">
        Selecciona un curso para ver el reporte de asistencia.
    </div>
    @endif
</div>
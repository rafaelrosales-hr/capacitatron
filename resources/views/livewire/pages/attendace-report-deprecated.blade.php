<?php

use App\Models\Course;
use App\Models\CourseHasUser;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public int $selectedCourseId = 0;
    public $attendanceReport = [];

    public function getCoursesProperty()
    {
        return Course::query()->orderBy('name')->get();
    }

    public function updatedSelectedCourseId(): void
    {
        $this->loadAttendanceReport();
    }

    public function loadAttendanceReport(): void
    {
        if ($this->selectedCourseId <= 0) {
            $this->attendanceReport = [];
            return;
        }

        $course = Course::find($this->selectedCourseId);

        if (!$course) {
            $this->attendanceReport = [];
            return;
        }

        $allUsers = User::query()->orderBy('name')->get();

        $courseRecords = CourseHasUser::where('course_id', $this->selectedCourseId)->get()->keyBy('user_id');

        $this->attendanceReport = $allUsers
            ->map(function ($user) use ($courseRecords) {
                $userId = $user->id;
                $record = $courseRecords->get($userId);

                $hasAttendance = $record && $record->completed_at !== null;

                $userMarkedAsTaken = $record && $record->attended === true;

                return [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'user_surname' => $user->surname ?? '',
                    'user_email' => $user->email,
                    'has_attendance_record' => $hasAttendance,
                    'user_marked_as_taken' => $userMarkedAsTaken,
                    'completed_at' => $record?->completed_at,
                    'attended' => $record?->attended,
                ];
            })
            ->toArray();
    }

    public function mount(): void
    {
        $this->loadAttendanceReport();
    }

    public function getStatsProperty()
    {
        if ($this->selectedCourseId <= 0 || empty($this->attendanceReport)) {
            return [
                'total' => 0,
                'with_attendance' => 0,
                'user_marked_taken' => 0,
                'both_match' => 0,
                'mismatch' => 0,
            ];
        }

        $total = count($this->attendanceReport);
        $withAttendance = count(array_filter($this->attendanceReport, fn($user) => $user['has_attendance_record'] === true));
        $userMarkedTaken = count(array_filter($this->attendanceReport, fn($user) => $user['user_marked_as_taken'] === true));
        $bothMatch = count(array_filter($this->attendanceReport, fn($user) => $user['has_attendance_record'] === true && $user['user_marked_as_taken'] === true));
        $mismatch = count(array_filter($this->attendanceReport, fn($user) => $user['has_attendance_record'] !== $user['user_marked_as_taken']));

        return [
            'total' => $total,
            'with_attendance' => $withAttendance,
            'user_marked_taken' => $userMarkedTaken,
            'both_match' => $bothMatch,
            'mismatch' => $mismatch,
        ];
    }
}; ?>

<div class="space-y-6 bg-slate-950 min-h-screen p-6">
    {{-- CABECERA Y FILTRO --}}
    <section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-xl">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-400">Reporte de Asistencia</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-100" style="font-family: 'Syne', sans-serif;">Asistencia por Curso</h1>
            </div>

            <div class="w-full max-w-md">
                <label class="mb-2 block text-sm font-medium text-white">Selecciona un curso</label>
                <select wire:model.live="selectedCourseId"
                    style="background-color: #ffffff !important; color: #000000 !important; border-color: #334155 !important;"
                    class="w-full rounded-xl px-4 py-3 text-sm font-bold transition focus:border-cyan-400 focus:ring-2 focus:ring-cyan-500/30 focus:outline-none">
                    <option value="0" class="text-black">-- Elige un curso --</option>
                    @foreach ($this->courses as $course)
                        <option value="{{ $course->id }}" class="text-black">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    @if ($selectedCourseId > 0)
        <div style="display: flex; gap: 1rem; width: 100%; flex-wrap: wrap; font-family: 'Syne', sans-serif;">
            @foreach ([['total', 'Total usuarios', 'text-white'], ['with_attendance', 'Con asistencia', 'text-blue-400'], ['user_marked_taken', 'Marcados por usuario', 'text-green-400'], ['both_match', 'Coinciden', 'text-purple-400'], ['mismatch', 'Discrepancia', 'text-amber-400']] as [$key, $label, $color])
                <div style="flex: 1 1 150px;" class="rounded-xl bg-slate-900 border border-slate-800 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $label }}
                    </p>
                    <p class="mt-2 text-2xl font-bold {{ $color }}" style="font-family: 'Syne', sans-serif;">{{ $this->stats[$key] }}</p>
                </div>
            @endforeach
        </div>

        {{-- TABLA --}}
        <div class="rounded-2xl border border-slate-800 bg-slate-950 p-6 shadow-2xl">
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-cyan-400">Detalle de asistencia</p>
                <h2 class="mt-1 text-xl font-semibold text-white">Usuarios del curso</h2>
            </div>

            @if (!empty($this->attendanceReport))
                <div class="overflow-hidden rounded-xl border border-slate-800">
                    <table class="min-w-full divide-y divide-slate-800 text-left text-sm text-slate-300">
                        <thead class="bg-slate-900 text-xs uppercase tracking-[0.24em] text-slate-400">
                            <tr>
                                <th class="px-4 py-3">Usuario</th>
                                <th class="px-4 py-3">Correo</th>
                                <th class="px-4 py-3 text-center">Asistencia</th>
                                <th class="px-4 py-3 text-center">Marcado</th>
                                <th class="px-4 py-3 text-center">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800 bg-slate-950">
                            @foreach ($this->attendanceReport as $user)
                                <tr class="hover:bg-slate-900/60">
                                    <td class="px-4 py-3 font-medium text-white">{{ $user['user_name'] }}
                                        {{ $user['user_surname'] }}</td>
                                    <td class="px-4 py-3 text-slate-400">{{ $user['user_email'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border {{ $user['has_attendance_record'] ? 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20' }}">
                                            {{ $user['has_attendance_record'] ? 'Asistió' : 'Sin registro' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold border {{ $user['user_marked_as_taken'] ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20' }}">
                                            {{ $user['user_marked_as_taken'] ? 'Tomado' : 'No tomado' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-400">
                                        {{ $user['completed_at'] ? \Carbon\Carbon::parse($user['completed_at'])->format('d/m/Y H:i') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @else
        <div
            class="rounded-2xl border border-dashed border-slate-700 bg-slate-900 px-6 py-10 text-center text-sm text-slate-400">
            Selecciona un curso para ver el reporte de asistencia.
        </div>
    @endif
</div>

<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Support\Collection;

new #[Layout('layouts.app')] class extends Component {
    public $currentMonth;
    public $currentYear;
    public $selectedDate = null;
    public $coursesForDate = [];
    public $showModal = false;

    public function mount(): void
    {
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
    }

    public function goToPreviousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->closeModal();
    }

    public function goToNextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        $this->closeModal();
    }

    public function selectDate($day): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, $day)->startOfDay();
        $this->selectedDate = $date->format('Y-m-d');

        // Usamos EXACTAMENTE la misma fuente y la misma regla que pinta el calendario,
        // así modal y celdas nunca pueden divergir.
        $this->coursesForDate = $this->getCoursesForDay($date)->values();

        $this->showModal = $this->coursesForDate->isNotEmpty();
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedDate = null;
        $this->coursesForDate = [];
    }

    #[Computed]
    public function coursesForMonth(): Collection
    {
        $startDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = Carbon::create($this->currentYear, $this->currentMonth, 1)->endOfMonth();

        return Course::whereNotNull('start_course')
            ->where(function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('start_course', [$startDate, $endDate])
                    ->orWhereBetween('end_course', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate) {
                        $q->where('start_course', '<=', $startDate)->whereNull('end_course');
                    })
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_course', '<=', $startDate)->where('end_course', '>=', $endDate);
                    });
            })
            ->with('subjetArea')
            ->get(['id', 'name', 'start_course', 'end_course', 'start_time', 'format', 'subject_area_key', 'is_active', 'teacher', 'course_link']);
    }

    protected function courseOccursOnDate($course, Carbon $date): bool
    {
        $start = $course->start_course?->copy()->startOfDay();
        if (!$start) {
            return false;
        }

        $end = $course->end_course?->copy()->startOfDay() ?? $start;

        if ($end->lt($start)) {
            $end = $start;
        }

        return $date->gte($start) && $date->lte($end);
    }

    public function getCoursesForDay($date)
    {
        $dateCarbon = $date->copy()->startOfDay();

        return $this->coursesForMonth->filter(fn($course) => $this->courseOccursOnDate($course, $dateCarbon));
    }

    public function getCourseTime($course): ?string
    {
        if (!$course->start_time) {
            return null;
        }

        return is_string($course->start_time) ? Carbon::parse($course->start_time)->format('H:i') : $course->start_time->format('H:i');
    }

    public function getCalendarDaysProperty()
    {
        $days = [];
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $startDay = $firstDay->copy()->startOfWeek(Carbon::SUNDAY);

        for ($i = 0; $i < 42; $i++) {
            $currentDay = $startDay->copy()->addDays($i);
            $days[] = [
                'date' => $currentDay,
                'is_current_month' => $currentDay->month === $this->currentMonth,
                'is_today' => $currentDay->isToday(),
                'day_number' => $currentDay->day,
            ];
        }

        return $days;
    }

    public function getMonthNameProperty(): string
    {
        return Carbon::create($this->currentYear, $this->currentMonth, 1)->locale('es')->monthName;
    }
}; ?>

<div class="flex flex-col gap-6 min-h-screen p-6 font-montserrat w-full" wire:keydown.escape.window="closeModal">

    <div class="rounded-2xl bg-transparent p-6">

        <!-- Cabecera -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-text font-syne">Calendario de Cursos</h2>
            <p class="mt-1 mb-6 text-sm text-text-muted">Visualiza los nombres y horarios de los cursos programados</p>

            <div class="flex items-center justify-between">
                <button type="button" wire:click="goToPreviousMonth"
                    class="flex items-center gap-2 px-4 py-2 bg-accent/15 border border-border rounded-xl text-sm font-medium text-accent hover:bg-accent/25 transition-colors cursor-pointer">
                    Anterior
                </button>

                <h3 class="text-xl font-bold text-text capitalize font-syne">
                    {{ $this->month_name }} {{ $currentYear }}
                </h3>

                <button type="button" wire:click="goToNextMonth"
                    class="flex items-center gap-2 px-4 py-2 bg-accent/15 border border-border rounded-xl text-sm font-medium text-accent hover:bg-accent/25 transition-colors cursor-pointer">
                    Siguiente
                </button>
            </div>
        </div>

        <!-- Rejilla del Calendario -->
        <div class="rounded-2xl border border-border bg-background p-4">
            <div class="grid grid-cols-7 mb-2 text-center">
                @foreach (['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'] as $dayName)
                <div class="text-xs font-semibold uppercase text-text-muted py-2">
                    {{ $dayName }}
                </div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-1.5">
                @foreach ($this->calendar_days as $day)
                @php
                $courses = $this->getCoursesForDay($day['date']);
                $isSelected = $selectedDate && $day['date']->format('Y-m-d') === $selectedDate;

                $cellClasses = 'bg-surface-hover/30 border-border';

                if (!$day['is_current_month']) {
                $cellClasses = 'bg-surface-hover/10 border-border opacity-35 cursor-not-allowed';
                } elseif ($courses->isNotEmpty()) {
                $cellClasses = 'bg-accent/10 border-accent/30 hover:border-accent/60';
                }

                if ($isSelected && $showModal) {
                $cellClasses .= ' ring-2 ring-accent';
                }
                if ($day['is_today']) {
                $cellClasses .= ' border-emerald-500/60';
                }
                @endphp

                <div wire:click="selectDate({{ $day['day_number'] }})"
                    class="min-h-[90px] p-2 rounded-xl border flex flex-col relative transition-all {{ $cellClasses }} {{ $day['is_current_month'] ? 'cursor-pointer' : '' }}">
                    <div class="flex justify-between items-start">
                        <span
                            class="text-sm font-bold {{ $day['is_current_month'] ? 'text-text' : 'text-text-muted' }}">
                            {{ $day['day_number'] }}
                        </span>
                        @if ($day['is_today'])
                        <span
                            class="text-[10px] bg-emerald-500 text-white px-1.5 py-0.5 rounded-full font-bold">
                            HOY
                        </span>
                        @endif
                    </div>

                    @if ($courses->isNotEmpty() && $day['is_current_month'])
                    <div class="mt-auto flex flex-col gap-0.5 overflow-hidden">
                        @foreach ($courses as $course)
                        @php $courseTime = $this->getCourseTime($course); @endphp
                        <span
                            class="flex items-center gap-1 text-[10px] bg-accent text-white px-1.5 py-0.5 rounded font-semibold whitespace-nowrap overflow-hidden">
                            @if ($courseTime)
                            <span class="font-bold shrink-0">{{ $courseTime }}</span>
                            @endif
                            <span class="overflow-hidden text-ellipsis">{{ $course->name }}</span>
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <!-- Modal de Detalle de Cursos -->
        @if ($showModal && $selectedDate)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fadeIn">
            <!-- Capa posterior para cerrar al hacer clic afuera -->
            <div class="fixed inset-0" wire:click="closeModal"></div>

            <div
                class="relative w-full max-w-lg rounded-2xl border border-border bg-background p-6 shadow-2xl z-10">
                <!-- Cabecera del Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-border">
                    <div>
                        <h3 class="text-lg font-bold text-text font-syne">Cursos programados</h3>
                        <p class="text-xs text-text-muted mt-0.5">
                            {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                        </p>
                    </div>
                    <button type="button" wire:click="closeModal"
                        class="p-1.5 rounded-xl text-text-muted hover:text-text hover:bg-surface-hover/50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Contenido con lista de cursos -->
                <div class="mt-4 max-h-[60vh] overflow-y-auto flex flex-col gap-3 pr-1">
                    @foreach ($coursesForDate as $course)
                    @php $timeFormatted = $this->getCourseTime($course); @endphp
                    <div
                        class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-surface-hover/20 border border-border hover:border-accent/40 transition-colors">
                        <div class="flex flex-col gap-1 min-w-0">
                            <span class="text-sm font-semibold text-text">{{ $course->name }}</span>
                            @if ($course->subjetArea)
                            <span
                                class="text-xs text-text-muted">{{ $course->subjetArea->name ?? '' }}</span>
                            @endif
                            @if ($course->format)
                            <span class="text-[11px] text-text-muted/80 capitalize">Modalidad:
                                {{ $course->format }}</span>
                            @endif
                            @if ($course->teacher)
                            <span class="text-[11px] text-text-muted/80">Ponente: <span
                                    class="font-bold font-syne">{{ $course->teacher }}</span></span>
                            @endif

                            <!-- Link al curso -->
                            <span class="text-[11px] text-text-muted/80 truncate">
                                Link:
                                @if (filled($course->course_link) && \Illuminate\Support\Str::startsWith($course->course_link, ['http://', 'https://']))
                                <a href="{{ $course->course_link }}" target="_blank" rel="noopener noreferrer"
                                    class="font-semibold text-accent underline hover:text-accent/80 break-all">
                                    {{ $course->course_link }}
                                </a>
                                @else
                                <span class="italic">Link no registrado</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($timeFormatted)
                            <span
                                class="text-xs font-bold text-accent bg-accent/15 border border-accent px-2.5 py-1 rounded-full">
                                {{ $timeFormatted }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
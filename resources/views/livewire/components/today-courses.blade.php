<?php

use App\Models\Course;
use Illuminate\Support\Carbon;
use function Livewire\Volt\{state, computed};

state(selectedDate: fn () => Carbon::today()->toDateString());

$coursesForDate = computed(function () {
    $today = Carbon::parse($this->selectedDate);

    return Course::query()
        ->where('is_active', true)
        ->whereDate('start_course', '<=', $today)
        ->whereDate('end_course', '>=', $today)
        ->with('subjetArea')
        ->orderBy('start_time')
        ->get();
});

$courseTime = fn (Course $course) => $course->start_time
    ? Carbon::parse($course->start_time)->format('h:i A')
    : null;

?>

<div class="w-full">
    <div class="pb-3 border-b border-border">
        <h3 class="text-lg font-bold text-text font-syne">Cursos programados</h3>
        <p class="text-xs text-text-muted mt-0.5">
            {{ \Carbon\Carbon::parse($selectedDate)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
        </p>
    </div>

    <div class="mt-4 max-h-[22rem] overflow-y-auto flex flex-col gap-3 pr-1">
        @forelse ($this->coursesForDate as $course)
            @php $timeFormatted = $this->courseTime($course); @endphp
            <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-background border border-border hover:border-accent/40 transition-colors">
                <div class="flex flex-col gap-1">
                    <span class="text-sm font-semibold text-text">{{ $course->name }}</span>
                    @if ($course->subjetArea)
                        <span class="text-xs text-text-muted">{{ $course->subjetArea->name ?? '' }}</span>
                    @endif
                    @if ($course->format)
                        <span class="text-[11px] text-text-muted/80 capitalize">Modalidad: {{ $course->format }}</span>
                    @endif
                    @if ($course->teacher)
                        <span class="text-[11px] text-text-muted/80">Ponente: <span class="font-bold font-syne">{{ $course->teacher }}</span></span>
                    @endif
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if ($timeFormatted)
                        <span class="text-xs font-bold text-accent bg-accent/15 border border-accent px-2.5 py-1 rounded-full">
                            {{ $timeFormatted }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center gap-2 py-8 text-center">
                <span class="text-3xl">🗓️</span>
                <p class="text-sm text-text-muted font-syne">
                    No existe algún curso programado el día de hoy.
                </p>
            </div>
        @endforelse
    </div>
</div>
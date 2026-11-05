<?php

use App\Models\Course;
use Illuminate\Support\Carbon;
use function Livewire\Volt\{computed};

$nextCourse = computed(function () {
    $today = Carbon::today()->toDateString();

    return Course::query()
        ->where('is_active', true)
        ->whereDate('start_course', '>', $today)
        ->with('subjetArea')
        ->orderBy('start_course')
        ->orderBy('start_time')
        ->first();
});

$courseTime = fn(Course $course) => $course->start_time
    ? Carbon::parse($course->start_time)->format('h:i A')
    : null;

?>

<div class="mt-4 pt-4 border-t border-border">
    <h3 class="text-lg font-bold text-text font-syne">Próximo curso</h3>

    <div class="mt-3">
        @if ($this->nextCourse)
        @php $course = $this->nextCourse; $timeFormatted = $this->courseTime($course); @endphp
        <div class="flex items-center justify-between gap-3 p-3.5 rounded-xl bg-background border border-border">
            <div class="flex flex-col gap-1">
                <span class="text-xs text-text-muted">
                    {{ \Carbon\Carbon::parse($course->start_course)->locale('es')->isoFormat('D [de] MMMM [de] YYYY') }}
                </span>
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
        @else
        <p class="text-sm text-text-muted font-syne">
            No hay próximos cursos programados.
        </p>
        @endif
    </div>
</div>
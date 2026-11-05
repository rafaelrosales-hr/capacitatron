<?php

use App\Models\Course;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public int $selectedCourseId = 0;
    public string $searchCourse = '';

    public function getCoursesProperty()
    {
        return Course::query()
            ->when(trim($this->searchCourse) !== '', fn($q) => $q->where('name', 'like', '%' . trim($this->searchCourse) . '%'))
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}; ?>

<div class="flex flex-col gap-6 min-h-screen p-6 font-montserrat w-full">

    <!-- Sección Principal -->
    <section class="rounded-2xl border border-border bg-transparent shadow-sm p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-accent font-syne">
                    Pase de lista
                </p>
                <h1 class="mt-1 text-3xl font-bold text-text font-syne">
                    Tomar asistencia
                </h1>
            </div>

            <div class="w-full max-w-md flex flex-col gap-2">
                

                <select id="selected-course" wire:model.live="selectedCourseId"
                    class="w-full rounded-xl border border-border bg-background/50 backdrop-blur-md px-4 py-3 text-sm text-black outline-none cursor-pointer focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    <option value="0">-- Selecciona un curso --</option>

                    @foreach ($this->courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </section>

    <!-- Contenido Condicional -->
    @if ($selectedCourseId > 0)
        <div class="rounded-2xl border border-border  backdrop-background/10">
            <livewire:components.asistencia-usuarios :course-id="$selectedCourseId" :key="'asistencia-' . $selectedCourseId" />
        </div>
    @else
        <div
            class="rounded-2xl border border-dashed border-border bg-background/10 backdrop-blur-md p-10 text-center text-sm text-text-muted mb-6">
            Selecciona un curso para ver a los usuarios y marcar la asistencia.
        </div>
    @endif
</div>

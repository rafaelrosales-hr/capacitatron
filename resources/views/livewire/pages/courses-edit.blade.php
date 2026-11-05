<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Course;
use App\Models\SubjectArea;

new #[Layout('layouts.app')] class extends Component {
    public Course $course;

    public string $name = '';
    public string $duration = '';
    public string $start_time = '';
    public string $end_time = '';
    public string $start_course = '';
    public string $end_course = '';
    public string $format = '';
    public string $teacher = '';
    public string $agent_type = '';
    public string $rfc_agent = '';
    public string $training_objetive = '';
    public string $institution = '';
    public string $course_link = '';
    public ?int $subject_area_key = null;
    public bool $is_active = true;
    public array $subjectAreas = [];

    public function mount(Course $course): void
    {
        $this->course = $course;

        $this->name = $course->name;
        $this->duration = $course->duration;
        $this->start_time = $course->start_time ? $course->start_time->format('H:i') : '';
        $this->end_time = $course->end_time ? \Illuminate\Support\Carbon::parse($course->end_time)->format('H:i') : '';
        $this->start_course = $course->start_course ? $course->start_course->format('Y-m-d') : '';
        $this->end_course = $course->end_course ? $course->end_course->format('Y-m-d') : '';
        $this->format = $course->format;
        $this->teacher = $course->teacher ?? '';
        $this->agent_type = $course->agent_type ?? '';
        $this->rfc_agent = $course->rfc_agent ?? '';
        $this->training_objetive = $course->training_objetive ?? '';
        $this->institution = $course->institution ?? '';
        $this->course_link = $course->course_link ?? '';
        $this->subject_area_key = $course->subject_area_key;
        $this->is_active = $course->is_active;

        $this->subjectAreas = SubjectArea::where('is_active', true)
            ->orderBy('description')
            ->pluck('description', 'key')
            ->toArray();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'duration' => ['required', 'numeric', 'min:0'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'start_course' => ['nullable', 'date'],
            'end_course' => ['nullable', 'date', 'after_or_equal:start_course'],
            'format' => ['required', 'string', 'in:PRESENCIAL,ONLINE,HIBRIDO'],
            'teacher' => ['nullable', 'string', 'max:255'],
            'agent_type' => ['nullable', 'string', 'in:INTERNO,EXTERNO'],
            'rfc_agent' => ['nullable', 'string', 'max:13'],
            'training_objetive' => ['nullable', 'string'],
            'institution' => ['nullable', 'string', 'max:255'],
            'course_link' => ['nullable', 'url', 'max:2048'],
            'subject_area_key' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        // Si el campo queda vacío, se guarda como NULL (no como cadena vacía)
        $validated['course_link'] = filled($validated['course_link'] ?? null)
            ? trim($validated['course_link'])
            : null;

        $this->course->update($validated);

        $this->dispatch(
            'toast',
            title: 'Curso actualizado',
            message: 'Los cambios se guardaron exitosamente.'
        );

        $this->redirect(route('courses-manager'), navigate: true);
    }
}; ?>

<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-text font-syne">Editar Curso</h1>
            <p class="text-sm text-text-muted">Modifica los datos del curso seleccionado.</p>
        </div>
        <a
            href="{{ route('courses-manager') }}"
            wire:navigate
            class="px-4 py-2 text-sm font-medium text-text-muted bg-surface-hover/40 border border-border rounded-xl hover:bg-surface-hover transition-colors">
            Volver
        </a>
    </div>

    <!-- Formulario -->
    <form wire:submit="save" class="space-y-8 rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm p-8">

        <div class="space-y-6">
            <h2 class="text-sm font-bold text-text uppercase tracking-wide border-b border-border pb-2">Información general</h2>

            <div>
                <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Nombre del curso</label>
                <input
                    type="text"
                    wire:model="name"
                    class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                @error('name') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Institución</label>
                    <input
                        type="text"
                        wire:model="institution"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    @error('institution') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Área temática</label>
                    <select
                        wire:model="subject_area_key"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                        <option value="">Sin definir</option>
                        @foreach ($subjectAreas as $key => $areaName)
                        <option value="{{ $key }}">{{ $areaName }}</option>
                        @endforeach
                    </select>
                    @error('subject_area_key') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Objetivo de la capacitación</label>
                <textarea
                    wire:model="training_objetive"
                    rows="3"
                    class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all"></textarea>
                @error('training_objetive') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="space-y-6">
            <h2 class="text-sm font-bold text-text uppercase tracking-wide border-b border-border pb-2">Duración y fechas</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Duración (horas)</label>
                    <input
                        type="number"
                        step="0.5"
                        min="0"
                        wire:model="duration"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    @error('duration') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Fecha de inicio del curso</label>
                    <input
                        type="date"
                        wire:model="start_course"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    @error('start_course') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Fecha de fin del curso</label>
                    <input
                        type="date"
                        wire:model="end_course"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    @error('end_course') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Hora de inicio</label>
                    <input
                        type="time"
                        wire:model="start_time"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    @error('start_time') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Hora de fin</label>
                    <input
                        type="time"
                        wire:model="end_time"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    @error('end_time') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <h2 class="text-sm font-bold text-text uppercase tracking-wide border-b border-border pb-2">Modalidad y agente capacitador</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Modalidad</label>
                    <select
                        wire:model="format"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                        <option value="PRESENCIAL">Presencial</option>
                        <option value="ONLINE">Online</option>
                        <option value="HIBRIDO">Híbrido</option>
                    </select>
                    @error('format') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Tipo de agente capacitador</label>
                    <select
                        wire:model="agent_type"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                        <option value="">Sin definir</option>
                        <option value="INTERNO">Interno</option>
                        <option value="EXTERNO">Externo</option>
                    </select>
                    @error('agent_type') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase text-text-muted mb-2">RFC del agente</label>
                    <input
                        type="text"
                        wire:model="rfc_agent"
                        maxlength="13"
                        class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text uppercase outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                    @error('rfc_agent') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <h2 class="text-sm font-bold text-text uppercase tracking-wide border-b border-border pb-2">Instructor y estado</h2>

            <div>
                <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Profesor / Instructor</label>
                <input
                    type="text"
                    wire:model="teacher"
                    class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                @error('teacher') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase text-text-muted mb-2">Link al curso</label>
                <input
                    type="url"
                    wire:model="course_link"
                    placeholder="https://..."
                    class="w-full px-4 py-2 text-sm bg-background border border-border rounded-xl text-text outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
                @error('course_link') <span class="text-xs text-accent mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <input
                    type="checkbox"
                    wire:model="is_active"
                    id="is_active"
                    class="w-4 h-4 rounded border-border bg-background text-accent focus:ring-accent focus:ring-offset-background cursor-pointer">
                <label for="is_active" class="text-sm font-medium text-text-muted cursor-pointer">Curso Activo</label>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-border">
            <button
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-accent rounded-xl hover:opacity-90 active:scale-[0.98] transition-all shadow-md cursor-pointer">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
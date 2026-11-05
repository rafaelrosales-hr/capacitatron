<x-app-layout>
    @php
        $authUser = auth()->user();
        $coursesTakenCount = $authUser?->coursesTakenCount() ?? 0;
        $coursesMissingCount = $authUser?->coursesMissingCount() ?? 0;
        $newCollaborators = \App\Models\User::newThisMonth()->count();
        $totalCollaborators = \App\Models\User::count();
        $terminatedCollaborators = \App\Models\User::inactive()->count();
        $activeCollaborators = \App\Models\User::active()->count();
    @endphp

    <div class="w-full grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-border bg-background p-4 text-text shadow-sm">
            <a href="{{ route('my_courses') }}" class="flex h-full flex-col justify-between gap-3">
                <span class="text-xs font-semibold uppercase tracking-[0.24em] text-text font-syne">Cursos tomados</span>
                <span class="text-4xl font-bold text-text font-syne">{{ $coursesTakenCount }}</span>
            </a>
        </div>

        <div class="rounded-xl border border-border bg-background p-4 text-text shadow-sm">
            <div class="flex h-full flex-col justify-between gap-3">
                <span class="text-xs font-semibold uppercase tracking-[0.24em] text-text font-syne">Cursos
                    faltantes</span>
                <span class="text-4xl font-bold text-text font-syne">{{ $coursesMissingCount }}</span>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-background p-4 text-text shadow-sm">
            <a href="{{ route('course-requests') }}" class="flex h-full flex-col justify-between gap-3">
                <span class="text-xs font-semibold uppercase tracking-[0.24em] text-text font-syne">Solicitud de
                    cursos</span>
                <span
                    class="text-4xl font-bold text-text font-syne">{{ auth()->user()->additionalCourses()->count() }}</span>
            </a>
        </div>
    </div>

    {{-- Segunda fila de cards --}}
    @role('RRHH')
        <div class="mt-4 w-full grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-xl border border-border bg-background p-4 text-text shadow-sm">
                <div class="flex h-full flex-col justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-text font-syne">Total de
                        colaboradores</span>
                    <span class="text-4xl font-bold text-text font-syne">{{ $totalCollaborators }}</span>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-background p-4 text-text shadow-sm">
                <div class="flex h-full flex-col justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-text font-syne">Numero de colaboradores dados de baja</span>
                    <span class="text-4xl font-bold text-text font-syne">{{ $terminatedCollaborators }}</span>
                </div>
            </div>
            <div class="rounded-xl border border-border bg-background p-4 text-text shadow-sm">
                <div class="flex h-full flex-col justify-between gap-3">
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-text font-syne">Numero de colaboradores activos</span>
                    <span class="text-4xl font-bold text-text font-syne">{{ $activeCollaborators }}</span>
                </div>
            </div>
        </div>
    @endrole
    <div class="mt-4 flex gap-4 md:flex-row h-[40rem]">
        <div class="w-full rounded-xl border border-border bg-transparent p-4 text-text shadow-sm lg:w-2/3">
            <livewire:components.today-courses />
            <livewire:components.next-course />
        </div>

        <div class="w-full rounded-xl border border-border bg-transparent p-4 text-text shadow-sm lg:w-1/3">
            <livewire:components.courses-chart />
        </div>
    </div>
</x-app-layout>

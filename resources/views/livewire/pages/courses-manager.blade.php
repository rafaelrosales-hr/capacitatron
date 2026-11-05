<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Course;

new #[Layout('layouts.app')] class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'courses' => Course::query()
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('institution', 'like', '%' . $this->search . '%');
                })
                ->orderBy('name')
                ->paginate(10),
        ];
    }
}; ?>

<div class="w-full space-y-6">
    <!-- Encabezado y Buscador -->
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
        <div>
            <h1 class="text-xl font-bold text-text font-syne">Gestión de Cursos</h1>
            <p class="text-sm text-text-muted">Edita los cursos registrados en el sistema.</p>
        </div>

        <div class="relative w-full sm:w-72">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-text-muted absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar por nombre o institución..."
                class="w-full pl-9 pr-4 py-2 text-sm bg-background/50 border border-border rounded-xl text-black placeholder-text-muted outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all">
        </div>
    </div>

    <!-- Tabla -->
    <div class="w-full overflow-x-auto rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm">
        <table class="w-full text-left border-collapse text-sm text-text-muted">
            <thead class="bg-surface-hover/30 text-xs font-semibold uppercase text-text-muted border-b border-border">
                <tr>
                    <th scope="col" class="px-6 py-4 font-syne">Curso</th>
                    <th scope="col" class="px-6 py-4 text-center font-syne">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                @forelse($courses as $course)
                <tr wire:key="course-{{ $course->id }}" class="hover:bg-surface-hover/40 transition-colors duration-200">
                    <td class="px-6 py-4 font-medium text-text">{{ $course->name }}</td>
                    <td class="px-6 py-4 text-center">
                        <a
                        {{-- href="{{ route('courses.edit', $course) }}" --}}
                        href="{{ route('courses.edit', $course) }}"
                        wire:navigate
                        class="px-3 py-1.5 text-xs font-medium text-accent bg-accent/10 border border-accent rounded-lg hover:bg-accent/20 transition-colors"
                        >
                        Editar
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-text-muted">
                        No hay cursos registrados
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $courses->links() }}
    </div>
</div>
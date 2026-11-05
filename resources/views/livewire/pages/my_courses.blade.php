<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Course;
use App\Models\CourseHasUser;
use App\Models\AdditionalCourse;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CourseSignature;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

new #[Layout('layouts.app')] class extends Component {
    public $courses = [];
    public $selectedCourses = [];
    public $userCourses = [];
    public bool $selectAll = false;
    public string $search = '';

    public function mount(): void
    {
        $this->loadCourses();
        $this->loadUserCourses();
    }

    public function loadCourses(): void
    {
        $regularCourses = Course::where('year', now()->year)
            ->with('subjetArea')
            ->orderBy('start_course')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'start_date' => $course->start_course ? $course->start_course->format('d/m/Y') : 'N/A',
                    'start_time' => $course->start_time ? $course->start_time->format('H:i') : null,
                    'format' => $course->format,
                    'is_active' => (bool) $course->is_active,
                    'is_additional' => false,
                ];
            })
            ->toArray();

        // Cursos adicionales ya aprobados del usuario autenticado: se
        // muestran en la misma tabla pero siempre deshabilitados, ya que
        // no se marcan/desmarcan desde aquí.
        $additionalCourses = AdditionalCourse::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->orderBy('created_at')
            ->get()
            ->map(function ($course) {
                return [
                    'id' => 'additional-' . $course->id,
                    'name' => $course->name,
                    'start_date' => $course->created_at ? $course->created_at->format('d/m/Y') : 'N/A',
                    'start_time' => null,
                    'format' => 'ADICIONAL',
                    'is_active' => false,
                    'is_additional' => true,
                ];
            })
            ->toArray();

        $this->courses = array_merge($regularCourses, $additionalCourses);
    }

    public function loadUserCourses(): void
    {
        $this->userCourses = CourseHasUser::where('user_id', Auth::id())->pluck('course_id')->toArray();

        $this->selectedCourses = $this->userCourses;

        // "Seleccionar todos" evaluará sólo los cursos que estén activos
        $activeCourseIds = array_column(
            array_filter($this->courses, fn($c) => $c['is_active']),
            'id'
        );

        $this->selectAll = !empty($activeCourseIds) && count(array_intersect($activeCourseIds, $this->selectedCourses)) === count($activeCourseIds);
    }

    public function updatedSelectAll($value): void
    {
        $userId = Auth::id();

        // Solo operamos sobre cursos activos
        $activeCourseIds = array_column(
            array_filter($this->courses, fn($c) => $c['is_active']),
            'id'
        );

        if ($value) {
            foreach ($activeCourseIds as $courseId) {
                CourseHasUser::updateOrCreate(
                    ['user_id' => $userId, 'course_id' => $courseId],
                    ['attended' => true, 'completed_at' => now()],
                );
            }

            $this->dispatch('toast', title: 'Cursos actualizados', message: 'Todos los cursos activos han sido marcados como tomados.');
        } else {
            CourseHasUser::where('user_id', $userId)->whereIn('course_id', $activeCourseIds)->delete();

            $this->dispatch('toast', title: 'Cursos actualizados', message: 'Todos los cursos activos han sido desmarcados.');
        }

        $this->loadUserCourses();
    }

    public function toggleCourse($courseId): void
    {
        $course = collect($this->courses)->firstWhere('id', $courseId);

        // Previene la acción si el curso no está activo, o si es un curso
        // adicional (esos no se marcan/desmarcan desde aquí).
        if (!$course || !($course['is_active'] ?? false) || ($course['is_additional'] ?? false)) {
            return;
        }

        $userId = Auth::id();
        $userCourseRecord = CourseHasUser::where('user_id', $userId)->where('course_id', $courseId)->first();
        $courseName = $course['name'] ?? 'Curso';

        if ($userCourseRecord) {
            $userCourseRecord->delete();
            $this->dispatch('toast', title: 'Curso desmarcado', message: "\"{$courseName}\" se marcó como no tomado.");
        } else {
            CourseHasUser::create([
                'user_id' => $userId,
                'course_id' => $courseId,
                'attended' => true,
                'completed_at' => now(),
            ]);
            $this->dispatch('toast', title: 'Curso marcado', message: "\"{$courseName}\" se marcó como tomado.");
        }

        $this->loadUserCourses();
    }

    public function isCourseSelected($courseId): bool
    {
        // Los cursos adicionales aprobados siempre se muestran como "tomados".
        if (is_string($courseId) && str_starts_with($courseId, 'additional-')) {
            return true;
        }

        return in_array($courseId, $this->selectedCourses);
    }

    public function filteredCourses(): array
    {
        if (trim($this->search) === '') {
            return $this->courses;
        }

        $term = mb_strtolower($this->search);

        return array_values(array_filter($this->courses, function ($course) use ($term) {
            return str_contains(mb_strtolower($course['name']), $term);
        }));
    }

    /**
     * Genera y descarga el PDF/certificado firmado con el listado de cursos tomados.
     */
    public function signCertificate()
    {
        if (!$this->selectAll) {
            $this->dispatch(
                'toast',
                title: 'No es posible firmar',
                message: 'Debes marcar todos los cursos activos como tomados antes de firmar.'
            );
            return;
        }

        $user = Auth::user();

        $completedCoursesRecords = CourseHasUser::where('user_id', $user->id)
            ->where('attended', true)
            ->with('course')
            ->get();

        // Cursos adicionales ya aprobados: también cuentan para el
        // certificado, junto con los cursos regulares tomados.
        $approvedAdditionalCourses = AdditionalCourse::where('user_id', $user->id)
            ->where('status', 'approved')
            ->get();

        if ($completedCoursesRecords->isEmpty() && $approvedAdditionalCourses->isEmpty()) {
            $this->dispatch(
                'toast',
                title: 'Atención',
                message: 'No tienes cursos tomados/asistidos para generar un certificado.'
            );
            return;
        }

        $courseIds = array_merge(
            $completedCoursesRecords->pluck('course_id')->toArray(),
            $approvedAdditionalCourses->map(fn($course) => 'additional-' . $course->id)->toArray()
        );

        $totalHours = $completedCoursesRecords->sum(fn($record) => $record->course->duration ?? 0)
            + $approvedAdditionalCourses->sum(fn($course) => $course->duration ?? 0);

        $signedAt = now();
        $folio = 'CERT-' . $signedAt->format('Y') . '-' . strtoupper(Str::random(8));

        $hash = CourseSignature::buildHash($user->id, $courseIds, $signedAt->timestamp);

        $signature = CourseSignature::create([
            'folio' => $folio,
            'user_id' => $user->id,
            'hash' => $hash,
            'course_ids' => $courseIds,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'signed_at' => $signedAt,
        ]);

        $completedCourses = $completedCoursesRecords
            ->map(fn($record) => [
                'name' => $record->course->name ?? 'Curso',
                'duration' => $record->course->duration ?? 0,
                'completed_at' => $record->completed_at
                    ? $record->completed_at->format('d/m/Y')
                    : '—',
                'is_additional' => false,
            ])
            ->concat($approvedAdditionalCourses->map(fn($course) => [
                'name' => $course->name,
                'duration' => $course->duration ?? 0,
                'completed_at' => $course->updated_at
                    ? $course->updated_at->format('d/m/Y')
                    : '—',
                'is_additional' => true,
            ]));

        $verificationUrl = route('certificate.verify', $folio);

        $qrCode = new QrCode($verificationUrl);
        $writer = new PngWriter();
        $qrCodeBase64 = base64_encode($writer->write($qrCode)->getString());

        $pdf = Pdf::loadView('pdf.course-certificate', [
            'user'            => $user,
            'courses'         => $completedCourses,
            'totalHours'      => $totalHours, 
            'signedAt'        => $signedAt,
            'folio'           => $folio,
            'verificationUrl' => $verificationUrl,
            'qrCodeBase64'    => $qrCodeBase64,
        ]);

        $this->dispatch(
            'toast',
            title: 'Certificado generado',
            message: "Folio {$folio}. Tu certificado se descargó correctamente."
        );

        return response()->streamDownload(
            fn() => print($pdf->output()),
            'certificado-' . $folio . '.pdf'
        );
    }
}; ?>

<div class="w-full">
    <section class="space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-semibold text-text font-syne">Mis cursos</h2>
                <p class="text-[11px] text-text-muted">Marca los cursos que ya tomaste.</p>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- Buscador -->
                <div class="relative w-full sm:w-64">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Buscar curso..."
                        class="w-full bg-background/50 backdrop-blur-md border border-border rounded-xl pl-9 pr-3 py-1.5 text-xs text-text placeholder-text-muted outline-none focus:border-accent focus:bg-background/80 transition-all shadow-sm" />
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-text-muted absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                </div>

                <!-- Botón Firmar: solo aparece cuando todos los cursos requeridos están marcados -->
                @if ($selectAll)
                <button
                    type="button"
                    wire:click="signCertificate"
                    wire:loading.attr="disabled"
                    wire:target="signCertificate"
                    class="shrink-0 flex items-center gap-2 px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white text-xs font-semibold rounded-xl transition-all cursor-pointer whitespace-nowrap disabled:opacity-60 disabled:cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                        <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                        <path d="M2 2l7.586 7.586"></path>
                        <circle cx="11" cy="11" r="2"></circle>
                    </svg>
                    <span wire:loading.remove wire:target="signCertificate">Firmar</span>
                    <span wire:loading wire:target="signCertificate">Generando...</span>
                </button>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-border bg-background/40 backdrop-blur-md shadow-sm">
            <table class="w-full text-left text-xs">
                <thead class="bg-surface-hover/30 border-b border-border/60 text-text-muted uppercase text-[10px] font-bold tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 w-16">Tomado</th>
                        <th class="px-5 py-3.5">Nombre del Curso</th>
                        <th class="px-5 py-3.5">Formato</th>
                        <th class="px-5 py-3.5">Fecha de curso</th>
                        <th class="px-5 py-3.5">Hora</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border/40">
                    @forelse($this->filteredCourses() as $course)
                    <tr wire:key="course-{{ $course['id'] }}" class="hover:bg-surface-hover/40 transition-colors border border-border {{ !$course['is_active'] ? 'opacity-50 bg-surface-hover/10' : '' }}">
                        <td class="px-5 py-3">
                            <input
                                type="checkbox"
                                wire:change="toggleCourse('{{ $course['id'] }}')"
                                {{ $this->isCourseSelected($course['id']) ? 'checked' : '' }}
                                {{ !$course['is_active'] ? 'disabled' : '' }}
                                class="w-4 h-4 rounded border-border bg-background text-accent focus:ring-accent focus:ring-offset-background {{ !$course['is_active'] ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
                        </td>
                        <td class="px-5 py-3 font-medium text-text">
                            {{ $course['name'] }}
                            @if($course['is_additional'] ?? false)
                            <span class="ml-1.5 text-[10px] font-normal text-accent/80 uppercase tracking-wide">(Curso adicional)</span>
                            @elseif(!$course['is_active'])
                            <span class="ml-1.5 text-[10px] font-normal text-rose-400/80 uppercase tracking-wide">(Terminado)</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                    @if ($course['format'] === 'PRESCENCIAL') bg-emerald-500/10 text-emerald-500 border-emerald-500/20
                                    @elseif($course['format'] === 'ONLINE') bg-blue-500/10 text-blue-500 border-blue-500/20
                                    @elseif($course['format'] === 'ADICIONAL') bg-accent/10 text-accent border-accent/20
                                    @else bg-purple-500/10 text-purple-500 border-purple-500/20 @endif">
                                {{ $course['format'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-text-muted">{{ $course['start_date'] }}</td>
                        <td class="px-5 py-3 text-text-muted">
                            @if ($course['start_time'])
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-500/10 px-2.5 py-1 text-[10px] font-medium text-blue-500 border border-blue-500/20">
                                {{ $course['start_time'] }}
                            </span>
                            @else
                            <span class="text-text-muted">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            @if ($this->isCourseSelected($course['id']))
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 px-2.5 py-1 text-[10px] font-medium text-emerald-500 border border-emerald-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Tomado
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-hover/50 px-2.5 py-1 text-[10px] font-medium text-text-muted border border-border">
                                No tomado
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-text-muted">
                            @if ($search)
                            No se encontraron cursos para "{{ $search }}"
                            @else
                            No hay cursos disponibles
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
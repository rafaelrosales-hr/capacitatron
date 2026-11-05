<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use App\Models\AdditionalCourse;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app')] class extends Component {
    use WithFileUploads;

    public $additionalCourses = [];

    public $selectedCourseId = null;

    public $certificateUpload = null;
    public $showCertificateModal = false;

    public $invoiceUpload = null;
    public $showInvoiceModal = false;

    public function mount(): void
    {
        $this->loadAdditionalCourses();
    }

    public function loadAdditionalCourses(): void
    {
        $this->additionalCourses = AdditionalCourse::where('user_id', Auth::id())->latest()->get()->toArray();
    }

    /**
     * El componente CourseRequestForm dispara este evento al crear una
     * solicitud. No necesitamos saber nada de su estado interno, solo
     * refrescar la lista.
     */
    #[On('course-created')]
    public function refreshCourses(): void
    {
        $this->loadAdditionalCourses();
    }

    public function openCertificateModal($courseId): void
    {
        $this->selectedCourseId = $courseId;
        $this->showCertificateModal = true;
    }

    public function closeCertificateModal(): void
    {
        $this->reset(['certificateUpload', 'selectedCourseId', 'showCertificateModal']);
    }

    public function uploadCertificate(): void
    {
        $this->validate([
            'certificateUpload' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'certificateUpload.required' => 'Debes seleccionar un archivo.',
            'certificateUpload.mimes' => 'El certificado debe ser un archivo PDF, JPG o PNG.',
            'certificateUpload.max' => 'El certificado no debe superar los 5MB.',
        ]);

        $course = AdditionalCourse::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->findOrFail($this->selectedCourseId);

        $path = $this->certificateUpload->store('certificates/' . Auth::id(), 'public');

        $course->update(['certificate_path' => $path]);

        $this->dispatch(
            'toast',
            title: 'Certificado cargado',
            message: 'El certificado se cargó correctamente.'
        );

        $this->closeCertificateModal();
        $this->loadAdditionalCourses();
    }

    public function openInvoiceModal($courseId): void
    {
        $this->selectedCourseId = $courseId;
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal(): void
    {
        $this->reset(['invoiceUpload', 'selectedCourseId', 'showInvoiceModal']);
    }

    public function uploadInvoice(): void
    {
        $this->validate([
            'invoiceUpload' => 'required|file|mimes:pdf,xml|max:5120',
        ], [
            'invoiceUpload.required' => 'Debes seleccionar un archivo.',
            'invoiceUpload.mimes' => 'La factura debe ser un archivo PDF o XML.',
            'invoiceUpload.max' => 'La factura no debe superar los 5MB.',
        ]);

        $course = AdditionalCourse::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->findOrFail($this->selectedCourseId);

        $path = $this->invoiceUpload->store('invoices/' . Auth::id(), 'public');

        $course->update(['invoice_path' => $path]);

        $this->dispatch(
            'toast',
            title: 'Factura cargada',
            message: 'La factura se cargó correctamente.'
        );

        $this->closeInvoiceModal();
        $this->loadAdditionalCourses();
    }
}; ?>

<div class="flex flex-col gap-6 min-h-screen p-6 font-montserrat w-full">

    <div class="rounded-2xl  bg-transparent p-6">

        <div class="flex items-center justify-between mb-6 bg-background py-3 px-4 rounded-lg border border-border">
            <div>
                <h1 class="text-2xl font-bold text-text font-syne">Solicitudes de Cursos</h1>
                <p class="mt-1 text-sm  font-semibold text-accent">En este apartado puedes registrar una solicitud de curso que sea de tu interés. Completa los datos solicitados para que podamos revisar y dar seguimiento a tu propuesta</p>
            </div>
            <button
                type="button"
                wire:click="$dispatch('open-course-modal')"
                class="flex items-center gap-2 px-4 py-2 bg-accent/15 border border-border rounded-xl text-sm font-medium text-accent hover:bg-accent/25 transition-colors cursor-pointer">
                Nueva Solicitud
            </button>
        </div>

        @php
        $approvedCourses = collect($additionalCourses)->where('status', 'approved');
        @endphp

        <div class="w-full overflow-x-auto rounded-2xl border border-border bg-background">
            <table class="w-full text-left border-collapse text-sm text-text-muted">
                <thead class="bg-background text-xs font-semibold uppercase text-text-muted border-border">
                    <tr>
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Horas</th>
                        <th class="px-6 py-4">Institución</th>
                        <th class="px-6 py-4">Costo</th>
                        <th class="px-6 py-4 text-right">Factura</th>
                        <th class="px-6 py-4 text-right">Certificado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/40">
                    @forelse($approvedCourses as $course)
                    <tr class="hover:bg-surface-hover/30 transition-colors">
                        <td class="px-6 py-4 text-text">{{ $course['name'] }}</td>
                        <td class="px-6 py-4">{{ $course['duration'] }}</td>
                        <td class="px-6 py-4">{{ $course['institution'] }}</td>
                        <td class="px-6 py-4">
                            {{ $course['price'] !== null ? '$' . number_format((float) $course['price'], 2) : '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if ($course['invoice_path'])
                            <a
                                href="{{ asset('storage/' . $course['invoice_path']) }}"
                                target="_blank"
                                class="text-xs font-medium text-accent hover:underline">
                                Ver factura
                            </a>
                            @else
                            <button
                                type="button"
                                wire:click="openInvoiceModal({{ $course['id'] }})"
                                class="text-xs font-medium px-3 py-1.5 bg-accent/15 border border-border rounded-lg text-accent hover:bg-accent/25 transition-colors cursor-pointer">
                                Cargar factura
                            </button>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if (!$course['has_certification'])
                            <span class="text-xs text-text-muted">No aplica</span>
                            @elseif ($course['certificate_path'])
                            <a
                                href="{{ asset('storage/' . $course['certificate_path']) }}"
                                target="_blank"
                                class="text-xs font-medium text-accent hover:underline">
                                Ver certificado
                            </a>
                            @else
                            <button
                                type="button"
                                wire:click="openCertificateModal({{ $course['id'] }})"
                                class="text-xs font-medium px-3 py-1.5 bg-accent/15 border border-border rounded-lg text-accent hover:bg-accent/25 transition-colors cursor-pointer">
                                Cargar certificado
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-text-muted">No hay cursos aprobados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <livewire:courserequestform />

    @if ($showCertificateModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        wire:click.self="closeCertificateModal">
        <div class="w-full max-w-md rounded-2xl border border-border bg-background p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-text mb-4 font-syne">Cargar certificado</h3>

            <label class="block text-xs font-medium text-text-muted mb-1">
                Certificado (PDF, JPG o PNG, máx. 5MB)
            </label>
            <input
                type="file"
                wire:model="certificateUpload"
                accept=".pdf,.jpg,.jpeg,.png"
                class="w-full px-3 py-2 bg-background/50 border border-border rounded-lg text-text outline-none file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-accent/15 file:text-accent file:text-xs file:font-medium file:cursor-pointer cursor-pointer">

            <div wire:loading wire:target="certificateUpload" class="text-xs text-accent mt-1">
                Subiendo archivo...
            </div>

            @if ($certificateUpload)
            <div class="text-xs text-emerald-500 mt-1">
                Archivo listo: {{ $certificateUpload->getClientOriginalName() }}
            </div>
            @endif

            @error('certificateUpload')
            <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
            @enderror

            <div class="flex gap-3 mt-6">
                <button
                    type="button"
                    wire:click="closeCertificateModal"
                    class="flex-1 py-2 border border-border rounded-lg text-sm font-medium text-text-muted hover:bg-surface-hover/30 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="uploadCertificate"
                    class="flex-1 py-2 bg-accent hover:opacity-90 rounded-lg text-white text-sm font-semibold cursor-pointer">
                    Guardar
                </button>
            </div>
        </div>
    </div>
    @endif

    @if ($showInvoiceModal)
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        wire:click.self="closeInvoiceModal">
        <div class="w-full max-w-md rounded-2xl border border-border bg-background p-6 shadow-lg">
            <h3 class="text-lg font-semibold text-text mb-4 font-syne">Cargar factura</h3>

            <label class="block text-xs font-medium text-text-muted mb-1">
                Factura (PDF o XML, máx. 5MB)
            </label>
            <input
                type="file"
                wire:model="invoiceUpload"
                accept=".pdf,.xml"
                class="w-full px-3 py-2 bg-background/50 border border-border rounded-lg text-text outline-none file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:bg-accent/15 file:text-accent file:text-xs file:font-medium file:cursor-pointer cursor-pointer">

            <div wire:loading wire:target="invoiceUpload" class="text-xs text-accent mt-1">
                Subiendo archivo...
            </div>

            @if ($invoiceUpload)
            <div class="text-xs text-emerald-500 mt-1">
                Archivo listo: {{ $invoiceUpload->getClientOriginalName() }}
            </div>
            @endif

            @error('invoiceUpload')
            <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
            @enderror

            <div class="flex gap-3 mt-6">
                <button
                    type="button"
                    wire:click="closeInvoiceModal"
                    class="flex-1 py-2 border border-border rounded-lg text-sm font-medium text-text-muted hover:bg-surface-hover/30 transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="uploadInvoice"
                    class="flex-1 py-2 bg-accent hover:opacity-90 rounded-lg text-white text-sm font-semibold cursor-pointer">
                    Guardar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
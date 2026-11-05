<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use App\Models\AdditionalCourse;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

new class extends Component {
    use WithFileUploads;

    public bool $showModal = false;

    public $courseName = '';
    public $courseHours = '';
    public $institution = '';
    public $courseUrl = '';
    public $courseCost = '';
    public $certificate = null;
    public $hasCertification = false;

    /**
     * Cualquier botón en cualquier parte de la página puede abrir este
     * modal disparando el evento global "open-course-modal", sin necesidad
     * de que el padre conozca el estado interno de este componente.
     */
    #[On('open-course-modal')]
    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    private function resetForm(): void
    {
        $this->reset(['courseName', 'courseHours', 'institution', 'courseUrl', 'courseCost', 'certificate', 'hasCertification']);
        $this->resetValidation();
    }

    public function submit(): void
    {
        $this->validate(
            [
                'courseName' => 'required|string|max:255',
                'courseHours' => 'required|numeric|min:0.1|max:1000',
                'institution' => 'required|string|max:255',
                'courseUrl' => ['nullable', 'string', 'max:1000', 'regex:/^https?:\/\/[^\s<>"]+$/i'],
                'courseCost' => 'required|numeric|min:0|max:999999.99',
                'hasCertification' => 'boolean',
                // El certificado no es obligatorio: el curso puede entregarlo
                // hasta su finalización.
                'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            ],
            [
                // Mensajes específicos
                'courseName.required' => 'El nombre del curso es obligatorio.',
                'courseName.max' => 'El nombre del curso no debe superar los 255 caracteres.',

                'courseHours.required' => 'Las horas del curso son obligatorias.',
                'courseHours.numeric' => 'Las horas deben ser un número válido.',
                'courseHours.min' => 'Las horas deben ser mayores a 0.',
                'courseHours.max' => 'Las horas no deben superar las 1000.',

                'institution.required' => 'La institución es obligatoria.',
                'institution.max' => 'La institución no debe superar los 255 caracteres.',

                'courseUrl.max' => 'La URL no debe superar los 1000 caracteres.',
                'courseUrl.regex' => 'La URL del curso no es válida. Debe iniciar con http:// o https://',

                'courseCost.required' => 'Debes indicar el costo del curso (IVA incluido).',
                'courseCost.numeric' => 'El costo debe ser un número válido.',
                'courseCost.min' => 'El costo no puede ser negativo.',
                'courseCost.max' => 'El costo no debe superar $999,999.99.',

                'certificate.file' => 'El certificado debe ser un archivo válido.',
                'certificate.mimes' => 'El certificado debe ser un archivo PDF, JPG o PNG.',
                'certificate.max' => 'El certificado no debe superar los 5MB.',

                // Respaldo genérico por si alguna regla no tiene mensaje específico
                'required' => 'El campo :attribute es obligatorio.',
                'string' => 'El campo :attribute debe ser texto.',
                'numeric' => 'El campo :attribute debe ser un número.',
                'boolean' => 'El campo :attribute no es válido.',
            ],
            [
                // Nombres amigables para :attribute
                'courseName' => 'nombre del curso',
                'courseHours' => 'horas del curso',
                'institution' => 'institución',
                'courseUrl' => 'URL del curso',
                'courseCost' => 'costo del curso',
                'hasCertification' => 'certificación',
                'certificate' => 'certificado',
            ],
        );

        $certificatePath = null;
        if ($this->certificate) {
            $certificatePath = $this->certificate->store('certificates/' . Auth::id(), 'public');
        }

        $course = AdditionalCourse::create([
            'name' => $this->courseName,
            'duration' => $this->courseHours,
            'institution' => $this->institution,
            'has_certification' => $this->hasCertification,
            'url_course' => $this->courseUrl,
            'price' => $this->courseCost,
            'invoice_path' => null,
            'certificate_path' => $certificatePath,
            'user_id' => Auth::id(),
        ]);

        $this->sendEmailNotification($course);

        // Avisa a quien esté escuchando (p. ej. la tabla de solicitudes)
        // que hay un curso nuevo, para que pueda refrescarse.
        $this->dispatch('course-created');

        $this->dispatch('toast', title: 'Solicitud enviada', message: 'Tu solicitud de curso se envió exitosamente.');

        $this->resetForm();
        $this->showModal = false;
    }

    private function sendEmailNotification($course): void
    {
        $user = Auth::user();
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT');
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'));
            $mail->addAddress('recursoshumanos@hrmedia.com.mx', 'Recursos Humanos');
            $mail->isHTML(true);
            $mail->Subject = 'Nueva solicitud de curso de: ' . $user->name . ' ' . $user->surname . ' ' . $user->second_surname;

            $certificateRow = $course->certificate_path
                ? '<tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px; width: 140px;">Certificado</td>
                    <td style="padding: 10px 0; font-size: 14px;">
                        <a href="' .
                asset('storage/' . $course->certificate_path) .
                '" style="color: #2563eb; text-decoration: none; font-weight: 600;">Ver certificado &rarr;</a>
                    </td>
                    </tr>'
                : '<tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px; width: 140px;">Certificado</td>
                    <td style="padding: 10px 0; font-size: 14px; color: #94a3b8;">Pendiente (se entrega al finalizar el curso)</td>
                    </tr>';

            $invoiceRow = '<tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px; width: 140px;">Factura</td>
                    <td style="padding: 10px 0; font-size: 14px; color: #94a3b8;">Pendiente (se carga una vez aprobada la solicitud)</td>
                    </tr>';

            $urlRow = $course->url_course
                ? '<tr>
                    <td style="padding: 10px 0; color: #64748b; font-size: 13px; width: 140px;">URL del curso</td>
                    <td style="padding: 10px 0; font-size: 14px;">
                        <a href="' .
                $course->url_course .
                '" style="color: #2563eb; text-decoration: none;">' .
                $course->url_course .
                '</a>
                    </td>
                </tr>'
                : '';

            $formattedCost = '$' . number_format((float) $course->price, 2) . ' MXN (IVA incluido)';

            $mail->Body =
                "
                <div style='font-family: Arial, Helvetica, sans-serif; background-color: #f1f5f9; padding: 32px 16px;'>
                    <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='max-width: 560px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);'>
                        <tr>
                            <td style='background-color: #E30051; background-image: linear-gradient(135deg, #E30051 0%, #EE7C00 100%); padding: 14px; text-align: center;'>
                                <h2> HR Media</h2>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 28px 32px 8px 32px;'>
                                <h2 style='margin: 0; font-size: 18px; color: #111827; font-weight: 700;'>Nueva solicitud de curso</h2>
                                <p style='margin: 6px 0 0 0; font-size: 14px; color: #64748b;'>Se ha registrado una nueva solicitud que requiere revisión.</p>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 32px;'>
                                <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color: #f8fafc; border-radius: 8px; padding: 16px;'>
                                    <tr>
                                        <td style='padding: 4px 0; color: #64748b; font-size: 13px; width: 140px;'>Usuario</td>
                                        <td style='padding: 4px 0; font-size: 14px; color: #111827; font-weight: 600;'>{$user->name} {$user->surname} {$user->second_surname}</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 4px 0; color: #64748b; font-size: 13px;'>Email</td>
                                        <td style='padding: 4px 0; font-size: 14px; color: #111827;'>{$user->email}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 0 32px 16px 32px;'>
                                <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='border-top: 1px solid #e2e8f0;'>
                                    <tr>
                                        <td colspan='2' style='padding-top: 16px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #2563eb;'>Detalle del curso</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px 0; color: #64748b; font-size: 13px; width: 140px;'>Curso</td>
                                        <td style='padding: 10px 0; font-size: 14px; color: #111827; font-weight: 600;'>{$course->name}</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px 0; color: #64748b; font-size: 13px;'>Duración</td>
                                        <td style='padding: 10px 0; font-size: 14px; color: #111827;'>{$course->duration} horas</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px 0; color: #64748b; font-size: 13px;'>Institución</td>
                                        <td style='padding: 10px 0; font-size: 14px; color: #111827;'>{$course->institution}</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px 0; color: #64748b; font-size: 13px;'>Costo</td>
                                        <td style='padding: 10px 0; font-size: 14px; color: #111827; font-weight: 600;'>{$formattedCost}</td>
                                    </tr>
                                    <tr>
                                        <td style='padding: 10px 0; color: #64748b; font-size: 13px;'>¿Expide certificado?</td>
                                        <td style='padding: 10px 0; font-size: 14px; color: #111827;'>
                                            <span style='display: inline-block; padding: 2px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background-color: " .
                ($course->has_certification ? '#dcfce7; color: #166534' : '#fee2e2; color: #991b1b') .
                ";'>" .
                ($course->has_certification ? 'Sí' : 'No') .
                "</span>
                                        </td>
                                    </tr>
                                    {$urlRow}
                                    {$invoiceRow}
                                    {$certificateRow}
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td style='padding: 16px 32px 24px 32px; border-top: 1px solid #e2e8f0;'>
                                <p style='margin: 0; font-size: 12px; color: #94a3b8;'>Este correo fue generado automáticamente por el sistema de solicitudes de cursos.</p>
                            </td>
                        </tr>
                    </table>
                </div>
                ";

            $mail->send();
        } catch (Exception $e) {
            Log::error('Error enviando correo: ' . $mail->ErrorInfo);
        }
    }
}; ?>

<div>
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-background/70 p-4"
        wire:click.self="closeModal">
        <div
            class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl border border-border bg-background p-6 shadow-lg">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-text font-syne">Nueva Solicitud de Curso</h2>
                <button type="button" wire:click="closeModal"
                    class="text-text-muted hover:text-text transition-colors cursor-pointer" aria-label="Cerrar">
                    &times;
                </button>
            </div>

            <form wire:submit="submit" class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-text-muted mb-1">Nombre del curso <span class="text-accent text-sm font-bold">*</span></label>
                    <input type="text" wire:model="courseName"
                        class="w-full px-3 py-2 bg-background/50 border border-border rounded-lg text-black outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors">
                    @error('courseName')
                    <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-text-muted mb-1">Horas del curso <span class="text-accent text-sm font-bold">*</span></label>
                    <input type="number" wire:model="courseHours" min="0.5" max="10000" step="0.5"
                        class="w-full px-3 py-2 bg-background/50 border border-border rounded-lg text-black outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors">
                    @error('courseHours')
                    <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-text-muted mb-1">Institución <span class="text-accent text-sm font-bold">*</span></label>
                    <input type="text" wire:model="institution"
                        class="w-full px-3 py-2 bg-background/50 border border-border rounded-lg text-black outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors">
                    @error('institution')
                    <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-text-muted mb-1">URL del curso</label>
                    <input type="url" wire:model="courseUrl" placeholder="https://..."
                        class="w-full px-3 py-2 bg-background/50 border border-border rounded-lg text-black outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors">
                    @error('courseUrl')
                    <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-text-muted mb-1">Costo del curso (IVA
                        incluido) <span class="text-accent text-sm font-bold">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-text-muted text-sm">$</span>
                        <input type="number" wire:model="courseCost" min="0" step="0.01"
                            placeholder="0.00"
                            class="w-full pl-7 pr-3 py-2 bg-background/50 border border-border rounded-lg text-black outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-colors">
                    </div>
                    @error('courseCost')
                    <span class="block text-xs text-red-500 mt-1">{{ $message }}</span>
                    @enderror
                </div>

                {{-- <div class="sm:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model.live="hasCertification"
                            class="w-4 h-4 rounded border-border bg-background text-accent focus:ring-accent focus:ring-offset-background cursor-pointer">
                        <span class="text-sm text-text-muted">¿El curso expide certificado?</span>
                    </label>
                </div> --}}

                <div class="sm:col-span-2">
                    <span class="block text-xs font-medium text-text-muted mb-2">¿El curso expide certificado?</span>
                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <span class="text-sm font-medium {{ $hasCertification ? 'text-text-muted' : 'text-text' }}">No</span>
                        <input type="checkbox" wire:model.live="hasCertification" class="sr-only">
                        <span aria-hidden="true"
                            style="position:relative; display:inline-block; flex-shrink:0; width:48px; height:26px; border-radius:9999px; transition:background-color .2s;
                                       background-color: {{ $hasCertification ? 'var(--color-accent, #ff6a00)' : '#4b5563' }};">
                            <span style="position:absolute; top:3px; left:3px; width:20px; height:20px; border-radius:9999px; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.4); transition:transform .2s; transform: translateX({{ $hasCertification ? '22px' : '0' }});"></span>
                        </span>
                        <span class="text-sm font-medium {{ $hasCertification ? 'text-text' : 'text-text-muted' }}">Sí</span>
                    </label>
                </div>

                @if ($hasCertification)
                <div class="sm:col-span-2">
                    <label class="block text-xs font-medium text-accent mb-1">
                        Si la solicitud es aprobada, posteriormente se tendrá que subir el certificado expedido
                        por la institución.
                    </label>
                </div>
                @endif

                <div class="sm:col-span-2 flex gap-3">
                    <button type="button" wire:click="closeModal"
                        class="flex-1 py-2.5 border border-border rounded-lg text-sm font-medium text-text-muted hover:bg-surface-hover/30 transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 bg-accent hover:opacity-90 active:scale-[0.98] transition-all rounded-lg text-white text-sm font-semibold cursor-pointer">
                        Enviar Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
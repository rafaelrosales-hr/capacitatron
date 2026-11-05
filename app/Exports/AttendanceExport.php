<?php

namespace App\Exports;

use App\Models\CourseHasUser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AttendanceExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(protected int $courseId) {}

    public function collection(): Collection
    {
        return CourseHasUser::query()
            ->where('course_id', $this->courseId)
            ->with('user')
            ->get()
            ->map(function (CourseHasUser $courseHasUser): array {
                $user = $courseHasUser->user;

                return [
                    'curp' => $user?->curp ?? '',
                    'nombre' => $user?->name ?? '',
                    'apellido_paterno' => $user?->surname ?? '',
                    'apellido_materno' => $user?->second_surname ?? '',
                    'email' => $user?->email ?? '',
                    'estado' => $user?->state ?? '',
                    'municipio' => $user?->municipality ?? '',
                    'ocupacion' => $user?->ocupation ?? '',
                    'asistio' => $courseHasUser->attended_rh ? 'Sí' : 'No',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'CURP',
            'Nombre',
            'Apellido Paterno',
            'Apellido Materno',
            'Email',
            'Estado',
            'Municipio',
            'Ocupación',
            'Asistió',
        ];
    }

    public function title(): string
    {
        return 'Asistencia';
    }
}

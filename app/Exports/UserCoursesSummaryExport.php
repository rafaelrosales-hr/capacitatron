<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class UserCoursesSummaryExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected $userId;

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    public function collection(): Collection
    {
        $rows = [];

        $query = User::query()->with(['courses' => function ($query) {
            $query->orderBy('name');
        }]);

        if ($this->userId) {
            $query->where('id', $this->userId);
        }

        $users = $query->orderBy('name')->get();

        foreach ($users as $user) {
            $attendedCourses = $user->courses
                ->filter(fn($course) => (bool) $course->pivot->attended_rh);

            if ($attendedCourses->isEmpty()) {
                $rows[] = [
                    'curp' => $user->curp,
                    'nombre' => $user->name,
                    'apellido_paterno' => $user->surname,
                    'apellido_materno' => $user->second_surname,
                    'estado' => $user->state,
                    'municipio' => $user->municipality,
                    'ocupacion' => $user->ocupation,
                    'estudios' => $user->education_level,
                    'doc_aprovatorio' => $user->major_title,
                    'intitucion' => $user->institution,
                    'curso' => 'Sin cursos asistidos',
                    'centro_trabajo' => $user->workplace
                ];

                continue;
            }

            foreach ($attendedCourses as $course) {
                $rows[] = [
                    'curp' => $user->curp,
                    'nombre' => $user->name,
                    'apellido_paterno' => $user->surname,
                    'apellido_materno' => $user->second_surname,
                    'estado' => $user->state,
                    'municipio' => $user->municipality,
                    'ocupacion' => $user->ocupation,
                    'estudios' => $user->education_level,
                    'doc_aprovatorio' => $user->major_title,
                    'intitucion' => $user->institution,
                    'curso' => $course->name,
                    'centro_trabajo' => $user->workplace
                ];
            }
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'CURP',
            'NOMBRE',
            'PRIMER APELLIDO',
            'SEGUNDO APELLIDO',
            'ESTADO',
            'MUNICIPIO',
            'OCUPACION',
            'NIV ESTUDIOS',
            'DOC APROVATORIO',
            'INSTITUCION',
            'CURSO',
            'CENTRO DE TRABAJO'
        ];
    }

    public function title(): string
    {
        return 'Cursos por Usuario';
    }
}

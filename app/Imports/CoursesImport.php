<?php

namespace App\Imports;

use App\Models\Course;
use App\Models\SubjectArea;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CoursesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Nombres de cursos que se omitieron por estar repetidos
     * (ya sea porque ya existían en BD o porque se repetían dentro del mismo archivo).
     *
     * @var array<int, string>
     */
    public array $skippedDuplicates = [];

    /**
     * Nombres (normalizados) ya vistos durante esta importación,
     * para detectar duplicados dentro del mismo archivo.
     *
     * @var array<string, true>
     */
    private array $seenInFile = [];

    /**
     * Mapa nombre normalizado del área => key.
     *
     * @var array<string, int>
     */
    private array $areasByName = [];

    /**
     * Keys válidas de áreas temáticas.
     *
     * @var array<int, true>
     */
    private array $validKeys = [];

    public function __construct()
    {
        // Se cargan una sola vez para no consultar la BD en cada fila
        foreach (SubjectArea::all(['key', 'name']) as $area) {
            $this->areasByName[$this->normalize($area->name)] = (int) $area->key;
            $this->validKeys[(int) $area->key] = true;
        }
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $name = isset($row['nombre_oficial_del_curso']) ? trim((string) $row['nombre_oficial_del_curso']) : null;

        if (blank($name)) {
            Log::warning('CoursesImport: fila omitida por falta de nombre', ['row' => $row]);
            return null;
        }

        $subjectAreaKey = $this->resolveSubjectAreaKey($row['area_tematica'] ?? null);

        if ($subjectAreaKey === null) {
            Log::warning('CoursesImport: fila omitida, area_tematica vacía o no existe en la base de datos', [
                'area_tematica' => $row['area_tematica'] ?? null,
                'curso'         => $name,
            ]);
            return null;
        }

        $normalizedName = mb_strtolower($name);

        if (isset($this->seenInFile[$normalizedName])) {
            $this->skippedDuplicates[] = $name;
            return null;
        }

        if (Course::whereRaw('LOWER(name) = ?', [$normalizedName])->exists()) {
            $this->skippedDuplicates[] = $name;
            return null;
        }

        $this->seenInFile[$normalizedName] = true;

        // Transformamos primero la fecha de inicio a una instancia Carbon
        $startCourse = $this->transformDate($row['inicio_del_curso'] ?? null);

        // Extraemos el año a partir de la fecha (o null si no viene fecha)
        $year = $startCourse ? $startCourse->year : null;

        return new Course([
            'name'              => $name,
            'duration'          => isset($row['duracion']) ? (float) $row['duracion'] : 0.0,
            'start_time'        => $this->transformTime($row['hora_de_inicio_del_curso'] ?? null),
            'start_course'      => $startCourse,
            'end_course'        => $this->transformDate($row['fin_del_curso'] ?? null),
            'year'              => $year,
            'agent_type'        => $this->sanitizeEnum($row['tipo_de_agente'] ?? null, ['INTERNO', 'EXTERNO', 'OTRO'], 'OTRO'),
            'rfc_agent'         => !blank($row['rfc_del_agente'] ?? null) ? trim((string) $row['rfc_del_agente']) : null,
            'format'            => $this->sanitizeEnum($row['modalidad'] ?? null, ['PRESENCIAL', 'ONLINE', 'HIBRIDO'], 'PRESENCIAL'),
            'training_objetive' => $row['objetivos'] ?? '',
            'institution'       => $row['institucion_que_imparte_el_curso'] ?? '',
            'teacher'           => $row['profesor'] ?? '',
            'subject_area_key'  => $subjectAreaKey,
            'course_link'       => $this->sanitizeUrl($row['link_al_curso'] ?? null),
            'is_active'         => true,
        ]);
    }

    /**
     * Obtiene la key del área temática a partir del nombre (o de la key numérica).
     * Devuelve null si está vacía o no existe.
     */
    private function resolveSubjectAreaKey($value): ?int
    {
        if (blank($value)) {
            return null;
        }

        // Si ya viene como número, se toma como key
        if (is_numeric($value)) {
            $key = (int) $value;
            return isset($this->validKeys[$key]) ? $key : null;
        }

        // Si viene como texto, se busca por nombre
        return $this->areasByName[$this->normalize((string) $value)] ?? null;
    }

    /**
     * Normaliza texto: sin acentos, minúsculas y espacios limpios.
     */
    private function normalize(?string $value): string
    {
        return mb_strtolower(Str::ascii(trim(preg_replace('/\s+/', ' ', (string) $value))));
    }

    /**
     * Convierte la hora enviada en formato Excel o Texto al formato MySQL HH:MM:SS
     */
    private function transformTime($value)
    {
        if (blank($value)) {
            return '00:00:00';
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                )->format('H:i:s');
            }

            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            return '00:00:00';
        }
    }

    /**
     * Convierte valores de fecha en instancias de Carbon para el tipo 'date'
     */
    private function transformDate($value)
    {
        if (blank($value)) {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::instance($value);
            }

            if (is_numeric($value)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                );
            }

            $value = trim((string) $value);

            // Formato día/mes/año (México)
            if (preg_match('#^\d{1,2}/\d{1,2}/\d{4}$#', $value)) {
                return Carbon::createFromFormat('d/m/Y', $value)->startOfDay();
            }

            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Sanitiza los valores ENUM asegurando mayúsculas y formateo limpio
     */
    private function sanitizeEnum($value, array $allowedValues, $default)
    {
        if (blank($value)) {
            return $default;
        }

        $formattedValue = mb_strtoupper(trim((string) $value));

        return in_array($formattedValue, $allowedValues, true) ? $formattedValue : $default;
    }

    /**
     * Devuelve la URL limpia o null si viene vacía o no es válida
     */
    private function sanitizeUrl($value): ?string
    {
        if (blank($value)) {
            return null;
        }

        $url = trim((string) $value);

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }
}

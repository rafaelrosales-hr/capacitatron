<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    // Mapas valor => curp dueña, para detectar duplicados sin consultar por fila
    private array $emails = [];
    private array $usernames = [];
    private array $employeeNumbers = [];
    private string $passwordEncriptada;

    public int $created = 0;
    public int $updated = 0;
    public array $errors = [];

    public function __construct()
    {
        User::select('curp', 'email', 'username', 'employee_number')->get()->each(function ($u) {
            if ($u->email)           $this->emails[strtolower($u->email)] = $u->curp;
            if ($u->username)        $this->usernames[$u->username] = $u->curp;
            if ($u->employee_number) $this->employeeNumbers[$u->employee_number] = $u->curp;
        });

        $this->passwordEncriptada = Hash::make('capmedia2026$');
    }

    public function collection(Collection $rows): void
    {
        $curps = $rows->map(fn($r) => strtoupper(trim((string) ($r['curp'] ?? ''))))
            ->filter()->unique()->values();

        $existentes = User::whereIn('curp', $curps)->get()->keyBy('curp');

        foreach ($rows as $i => $row) {
            $curp = strtoupper(trim((string) ($row['curp'] ?? '')));

            if ($curp === '') {
                continue;
            }

            if (strlen($curp) !== 18) {
                $this->errors[] = "CURP inválida: {$curp}";
                continue;
            }

            $user = $existentes->get($curp);

            try {
                $data = $this->mapRow($row, $curp, $user === null);

                if ($user) {
                    $user->update($data);
                    $this->updated++;
                } else {
                    $data['curp'] = $curp;
                    $data['password'] = $this->passwordEncriptada;
                    $data['second_surname'] = $data['second_surname'] ?? '';
                    $data['institution'] = $data['institution'] ?? 'PÚBLICA';
                    $data['is_active'] = $data['is_active'] ?? true;

                    $nuevo = User::create($data);
                    $existentes->put($curp, $nuevo);
                    $this->created++;
                }
            } catch (\Throwable $e) {
                $this->errors[] = "CURP {$curp}: " . $e->getMessage();
            }
        }
    }

    private function mapRow($row, string $curp, bool $esNuevo): array
    {
        $ocupacion = $this->clean($row['ocupacion'] ?? null);
        if ($ocupacion === '?') {
            $ocupacion = null;
        }

        $institucion = $this->clean($row['institucion'] ?? null);
        if ($institucion !== null) {
            $institucion = str_contains(strtoupper($institucion), 'PRIV') ? 'PRIVADA' : 'PÚBLICA';
        }

        $data = [
            'name'              => $this->clean($row['nombre'] ?? null),
            'surname'           => $this->clean($row['primer_apellido'] ?? null),
            'second_surname'    => $this->clean($row['segundo_apellido'] ?? null),
            'state'             => $this->clean($row['estado'] ?? null),
            'municipality'      => $this->clean($row['municipio'] ?? null),
            'ocupation'         => $ocupacion,
            'education_level'   => $this->clean($row['niv_estudios'] ?? null),
            'major_title'       => $this->clean($row['doc_probatorio'] ?? null),
            'institution'       => $institucion,
            'workplace'         => $this->clean($row['centro_de_trabajo'] ?? null),
            'area'              => $this->clean($row['area'] ?? null),
            'major'             => $this->clean($row['puesto'] ?? null),
            'major_description' => $this->clean($row['puesto_en_espanol'] ?? null),
            'is_active'         => $this->boolean($row['colaborador_activo'] ?? null),
        ];

        // Email: en altas nuevas sin correo se usa el de respaldo
        $email = $this->clean($row['correo'] ?? null);
        $email = $email ? strtolower($email) : null;
        if ($email === null && $esNuevo) {
            $email = strtolower($curp) . '@hrmedia.com.mx';
        }

        $data['email']           = $this->uniqueOrNull($this->emails, $email, $curp, 'correo');
        $data['username']        = $this->uniqueOrNull($this->usernames, $this->clean($row['nombre_de_usuario'] ?? null), $curp, 'usuario');
        $data['employee_number'] = $this->uniqueOrNull($this->employeeNumbers, $this->clean($row['numero_de_colaborador'] ?? null), $curp, 'núm. colaborador');

        // Las celdas vacías no pisan datos existentes (false de is_active sí se conserva)
        return array_filter($data, fn($v) => $v !== null);
    }

    /**
     * Devuelve el valor si está libre o ya es de esta misma CURP;
     * si pertenece a otra CURP lo descarta y lo reporta.
     */
    private function uniqueOrNull(array &$map, ?string $value, string $curp, string $label): ?string
    {
        if ($value === null) {
            return null;
        }

        if (isset($map[$value]) && $map[$value] !== $curp) {
            $this->errors[] = "CURP {$curp}: {$label} '{$value}' ya pertenece a otro colaborador, no se asignó.";
            return null;
        }

        $map[$value] = $curp;
        return $value;
    }

    private function clean(mixed $v): ?string
    {
        $v = trim((string) $v);

        return ($v === '' || in_array(strtoupper($v), ['NULL', 'N/A'], true)) ? null : $v;
    }

    private function boolean(mixed $v): ?bool
    {
        $v = $this->clean($v);

        if ($v === null) {
            return null;
        }

        $v = strtolower(strtr($v, ['Í' => 'i', 'í' => 'i']));

        return in_array($v, ['1', 'si', 's', 'activo', 'true', 'x'], true);
    }

    public function chunkSize(): int
    {
        return 200;
    }
}

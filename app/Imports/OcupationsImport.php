<?php

namespace App\Imports;

use App\Models\Ocupation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OcupationsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        return new Ocupation([
            'key'         => $row['clave'],
            'description' => $row['descripcion'],
        ]);
    }
}

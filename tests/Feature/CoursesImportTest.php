<?php

use App\Imports\CoursesImport;

it('omite un curso cuando la clave de área temática no existe', function () {
    $import = new CoursesImport();

    $row = [
        'nombre_oficial_del_curso' => 'Curso de prueba',
        'duracion' => 1.5,
        'inicio_del_curso' => '2026-07-04',
        'fin_del_curso' => '2026-07-04',
        'tipo_de_agente' => 'INTERNO',
        'rfc_del_agente' => 'ABC123456789',
        'modalidad' => 'PRESCENCIAL',
        'objetivos' => 'Objetivo de prueba',
        'institucion_que_imparte_el_curso' => 'HR Ratings Media S.A de C.V',
        'profesor' => 'Mauricio Pichardo',
        'area_tematica' => 177,
    ];

    expect($import->model($row))->toBeNull();
});

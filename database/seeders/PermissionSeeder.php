<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('permissions')->insert([
            [
                'name' => 'pase_lista',
                'guard_name' => 'web',
            ], //1
            [
                'name' => 'reporte',
                'guard_name' => 'web',
            ], //2
            [
                'name' => 'carga_usuarios',
                'guard_name' => 'web',
            ], //3 - root
            [
                'name' => 'cursos_media',
                'guard_name' => 'web',
            ], //4
            [
                'name' => 'curso_request',
                'guard_name' => 'web',
            ], //5
            [
                'name' => 'attendace_report',
                'guard_name' => 'web',
            ], //6
            [
                'name' => 'catalogos',
                'guard_name' => 'web',
            ], //7
        ]);
    }
}

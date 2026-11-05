<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'curp' => 'QURT900822MDFVZN07',
                'name' => 'Tania',
                'surname' => 'Quevedo',
                'second_surname' => 'Rozo',
                'state' => 'Ciudad de México',
                'municipality' => 'Mexico',
                'ocupation' => 4041,
                'education_level' => 'Licenciatura',
                'major_title' => 'Titulo',
                'institution' => 'PRIVADA',
                'workplace' => 'HR Media S.A de C.V',
                'area' => 'Recursos Humanos',
                'major' => 'Directora de Recursos Humanos y Recursos Materiales',
                'major_description' => 'Directora de Recursos Humanos y Recursos Materiales',
                'email' => 'tania.quevedo@hrmedia.com.mx',
                'password' => 'T4n1a*Qu3ved026_',
                'is_active' => true,
                'role' => 'RRHH'
            ],
            [
                'curp' => 'GUVF880727MDFZLR07',
                'name' => 'María Fernanda',
                'surname' => 'Guzmán',
                'second_surname' => 'Del valle',
                'state' => 'Ciudad de México',
                'municipality' => 'México',
                'ocupation' => 4041,
                'education_level' => 'Licenciatura',
                'major_title' => 'Titulo',
                'institution' => 'PRIVADA',
                'workplace' => 'HR Media S.A de C.V',
                'area' => 'Recursos Humanos',
                'major' => 'Directora Ejecutiva Sr. de Recursos Humanos y Recursos Materiales',
                'major_description' => 'Directora Ejecutiva Sr. de Recursos Humanos y Recursos Materiales',
                'email' => 'mariafernanda.guzman@hrmedia.com.mx',
                'password' => 'm4f3rGuzm4n2026$',
                'is_active' => true,
                'role' => 'RRHH'
            ],
            [
                'curp' => 'GAEG12345678WERTYU',
                'name' => 'Efren',
                'surname' => 'Garcia',
                'second_surname' => '',
                'state' => 'Estado de México',
                'municipality' => 'México',
                'ocupation' => 'Gerente de Produccion',
                'education_level' => 'Licenciatura',
                'major_title' => 'Ingeniería en Desarrollo y Gestión Software ',
                'institution' => 'PÚBLICA',
                'workplace' => 'HR Media S.A de C.V',
                'area' => 'TI',
                'major' => 'Gerente de Produccion',
                'major_description' => 'Gerente de Produccion',
                'email' => 'efren.garcia@hrmedia.com.mx',
                'password' => 'cursosadmin2026$',
                'is_active' => true,
                'role' => 'root'
            ],
            [
                'curp' => 'MUFL770915HDFNLS06',
                'name' => 'Luis',
                'surname' => 'Munguía',
                'second_surname' => 'Flores',
                'state' => 'Estado de México',
                'municipality' => 'México',
                'ocupation' => 'Gerente de Desarrollo',
                'education_level' => 'Licenciatura',
                'major_title' => 'Ingeniería en Informatica ',
                'institution' => 'PÚBLICA',
                'workplace' => 'HR Media S.A de C.V',
                'area' => 'TI',
                'major' => 'Gerente de Desarrollo',
                'major_description' => 'Gerente de Desarrollo',
                'email' => 'luis.munguia@hrmedia.com.mx',
                'password' => 'cursosadmin2026$',
                'is_active' => true,
                'role' => 'root'
            ],
            [
                'curp' => 'PANJ960125HMCDVR00',
                'name' => 'Jairo',
                'surname' => 'Padilla',
                'second_surname' => 'Nava',
                'state' => 'Estado de México',
                'municipality' => 'México',
                'ocupation' => 'Ejecutivo',
                'education_level' => 'Licenciatura',
                'major_title' => 'Ingeniería en Desarrollo y Gestión Software ',
                'institution' => 'PÚBLICA',
                'workplace' => 'HR Media S.A de C.V',
                'area' => 'TI',
                'major' => 'Ejecutivo',
                'major_description' => 'Ejecutivo de Desarrollo',
                'email' => 'jairo.padilla@hrmedia.com.mx',
                'password' => 'cursosadmin2026$',
                'is_active' => true,
                'role' => 'root'
            ],
            [
                'curp' => 'MALE951114HMCRPD03',
                'name' => 'Eduardo Aarón',
                'surname' => 'Márquez',
                'second_surname' => 'López',
                'state' => 'Estado de México',
                'municipality' => 'México',
                'ocupation' => 'Coordinador',
                'education_level' => 'Licenciatura',
                'major_title' => 'Ingeniería en Desarrollo y Gestión Software ',
                'institution' => 'PÚBLICA',
                'workplace' => 'HR Media S.A de C.V',
                'area' => 'TI',
                'major' => 'Coordinador',
                'major_description' => 'Coordinador de Desarrollo',
                'email' => 'eduardo.marquez@hrmedia.com.mx',
                'password' => 'cursosadmin2026$',
                'is_active' => true,
                'role' => 'root'
            ],
            [
                'curp' => 'RORR939394HMCSVF09',
                'name' => 'Rafael Eduardo',
                'surname' => 'Rosales',
                'second_surname' => 'Rivas',
                'state' => 'Estado de México',
                'municipality' => 'México',
                'ocupation' => 'Analista',
                'education_level' => 'Licenciatura',
                'major_title' => 'Ingeniería en Desarrollo y Gestión Software ',
                'institution' => 'PÚBLICA',
                'workplace' => 'HR Media S.A de C.V',
                'area' => 'TI',
                'major' => 'Analista',
                'major_description' => 'Analista de Desarrollo',
                'email' => 'rafael.rosales@hrmedia.com.mx',
                'password' => '1234qwer',
                'is_active' => true,
                'role' => 'root'
            ],
        ];

        foreach ($users as $userData) {

            $roleName = $userData['role'];
            unset($userData['role']);

            $userData['password'] = Hash::make($userData['password']);

            $user = User::updateOrCreate(
                ['curp' => $userData['curp']],
                $userData
            );

            $user->syncRoles([$roleName]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RolesAndUsersSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roles = [
            ['code' => 'ADMIN_TECH', 'name' => 'Administrador Técnico', 'description' => 'Configuración de usuarios, auditoría y respaldos'],
            ['code' => 'ADMIN_FUNC', 'name' => 'Administrador Funcional', 'description' => 'Configuración de empleadores, convenios y conceptos'],
            ['code' => 'LIQUIDADOR', 'name' => 'Liquidador', 'description' => 'Carga de novedades, simulación y cálculo de liquidaciones'],
            ['code' => 'REVISOR', 'name' => 'Revisor / Aprobador', 'description' => 'Control de cálculos, aprobación y cierre de liquidaciones'],
            ['code' => 'AUDITOR', 'name' => 'Auditor / Consulta', 'description' => 'Lectura de historial, reportes y auditoría'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['code' => $role['code']], array_merge($role, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 2. Permisos
        $permissions = [
            ['code' => 'users.manage', 'name' => 'Administrar usuarios y permisos'],
            ['code' => 'employers.manage', 'name' => 'Crear y editar empleadores'],
            ['code' => 'workforce.manage', 'name' => 'Administrar legajos y novedades'],
            ['code' => 'agreements.manage', 'name' => 'Configurar convenios y escalas'],
            ['code' => 'payroll.calculate', 'name' => 'Calcular liquidaciones borradores'],
            ['code' => 'payroll.approve', 'name' => 'Aprobar y cerrar liquidaciones'],
            ['code' => 'payroll.export', 'name' => 'Exportar TXT LSD y PDFs'],
            ['code' => 'audit.view', 'name' => 'Ver registro de auditoría'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(['code' => $permission['code']], array_merge($permission, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 3. Usuarios de prueba (5 operadores simultáneos para 2 oficinas)
        $users = [
            [
                'name' => 'Administrador General',
                'email' => 'admin@estudio.test',
                'password' => Hash::make('Password123!'),
                'status' => 'ACTIVE',
                'office' => 'Oficina Central (Sede 1)',
                'role_code' => 'ADMIN_TECH',
            ],
            [
                'name' => 'Liquidador Oficina 1 - Juan Pérez',
                'email' => 'operador1@estudio.test',
                'password' => Hash::make('Password123!'),
                'status' => 'ACTIVE',
                'office' => 'Sede 1 - Centro',
                'role_code' => 'LIQUIDADOR',
            ],
            [
                'name' => 'Liquidador Oficina 1 - Ana Gómez',
                'email' => 'operador2@estudio.test',
                'password' => Hash::make('Password123!'),
                'status' => 'ACTIVE',
                'office' => 'Sede 1 - Centro',
                'role_code' => 'LIQUIDADOR',
            ],
            [
                'name' => 'Liquidador Oficina 2 - Carlos Díaz',
                'email' => 'operador3@estudio.test',
                'password' => Hash::make('Password123!'),
                'status' => 'ACTIVE',
                'office' => 'Sede 2 - Sucursal',
                'role_code' => 'LIQUIDADOR',
            ],
            [
                'name' => 'Revisor General - Martín Rossi',
                'email' => 'revisor@estudio.test',
                'password' => Hash::make('Password123!'),
                'status' => 'ACTIVE',
                'office' => 'Sede 2 - Sucursal',
                'role_code' => 'REVISOR',
            ],
        ];

        foreach ($users as $userData) {
            $roleCode = $userData['role_code'];
            unset($userData['role_code']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            $role = DB::table('roles')->where('code', $roleCode)->first();
            if ($role) {
                DB::table('user_employer_roles')->updateOrInsert(
                    ['user_id' => $user->id, 'role_id' => $role->id, 'employer_id' => null],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }
}

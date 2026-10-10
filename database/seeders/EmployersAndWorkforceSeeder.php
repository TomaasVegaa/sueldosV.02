<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployersAndWorkforceSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        // 1. Empleador 1
        $emp1Id = DB::table('employers')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'cuit' => '30718096045',
            'legal_name' => 'EMPRESA COMERCIAL NORTE S.A.',
            'status' => 'ACTIVE',
            'lock_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employer_settings_versions')->insert([
            'employer_id' => $emp1Id,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
            'employer_type_code' => '1',
            'zone_code' => '84',
            'activity_code' => '049',
            'payment_place' => 'San Miguel de Tucumán',
            'contribution_bank' => 'Banco Macro S.A.',
            'receipt_template' => 1,
            'notes' => 'Comercio CCT 130/75',
            'created_by' => $admin?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('workplaces')->insert([
            'employer_id' => $emp1Id,
            'code' => '0001',
            'name' => 'Casa Central',
            'locality' => 'San Miguel de Tucumán',
            'address' => 'Av. San Martín 1234',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Empleador 2 (Para probar aislamiento estricto)
        $emp2Id = DB::table('employers')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'cuit' => '30654321098',
            'legal_name' => 'DISTRIBUIDORA DEL SUR S.R.L.',
            'status' => 'ACTIVE',
            'lock_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employer_settings_versions')->insert([
            'employer_id' => $emp2Id,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
            'employer_type_code' => '1',
            'zone_code' => '84',
            'activity_code' => '049',
            'payment_place' => 'Tafí Viejo, Tucumán',
            'contribution_bank' => 'Banco Santander',
            'receipt_template' => 1,
            'notes' => 'Comercio CCT 130/75',
            'created_by' => $admin?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Trabajador 1 (Empresa 1)
        $worker1Id = DB::table('workers')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'cuil' => '20351234567',
            'first_name' => 'Lucas',
            'last_name' => 'Rodríguez',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $emp1Worker1Id = DB::table('employments')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'employer_id' => $emp1Id,
            'worker_id' => $worker1Id,
            'file_number' => '0001',
            'hire_date' => '2022-03-01',
            'seniority_date' => '2022-03-01',
            'termination_date' => null,
            'status' => 'ACTIVE',
            'lock_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employment_versions')->insert([
            'employment_id' => $emp1Worker1Id,
            'employer_id' => $emp1Id,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
            'agreement_code' => '003',
            'category_code' => '019',
            'category_name' => 'VENDEDOR B',
            'workday_type' => 3, // 1/2 jornada
            'situation_code' => '01',
            'contract_mode_code' => '014',
            'health_insurer_code' => '126205', // OSECAC
            'activity_code' => '049',
            'zone_code' => '84',
            'condition_code' => '01',
            'employer_type_code' => '1',
            'cbu' => '2850000000000000000000',
            'agreed_salary' => 613174.50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Trabajador 2 (Empresa 2)
        $worker2Id = DB::table('workers')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'cuil' => '27409876543',
            'first_name' => 'Camila',
            'last_name' => 'Fernández',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $emp2Worker1Id = DB::table('employments')->insertGetId([
            'uuid' => (string) Str::uuid(),
            'employer_id' => $emp2Id,
            'worker_id' => $worker2Id,
            'file_number' => '0001',
            'hire_date' => '2024-05-15',
            'seniority_date' => '2024-05-15',
            'termination_date' => null,
            'status' => 'ACTIVE',
            'lock_version' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employment_versions')->insert([
            'employment_id' => $emp2Worker1Id,
            'employer_id' => $emp2Id,
            'effective_from' => '2026-01-01',
            'effective_to' => null,
            'agreement_code' => '003',
            'category_code' => '009',
            'category_name' => 'RECEPCIONISTA',
            'workday_type' => 4, // 1/4 jornada
            'situation_code' => '01',
            'contract_mode_code' => '014',
            'health_insurer_code' => '126205', // OSECAC
            'activity_code' => '049',
            'zone_code' => '84',
            'condition_code' => '01',
            'employer_type_code' => '1',
            'cbu' => '2850000000000000000001',
            'agreed_salary' => 346172.25,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

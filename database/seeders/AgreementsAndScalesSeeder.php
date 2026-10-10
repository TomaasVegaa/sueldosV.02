<?php

namespace Database\Seeders;

use App\Models\Agreement;
use App\Models\AgreementCategory;
use App\Models\CategoryScaleVersion;
use App\Models\User;
use Illuminate\Database\Seeder;

class AgreementsAndScalesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();

        // 1. Convenio Comercio 130/75
        $cct003 = Agreement::firstOrCreate(
            ['code' => '003'],
            [
                'name' => 'Comercio — CCT 130/75',
                'description' => 'Convenio Colectivo de Trabajo General Empleados de Comercio',
                'status' => 'ACTIVE',
            ]
        );

        // 2. Fuera de Convenio
        $cct001 = Agreement::firstOrCreate(
            ['code' => '001'],
            [
                'name' => 'Fuera de Convenio',
                'description' => 'Personal Jerárquico / Dirección',
                'status' => 'ACTIVE',
            ]
        );

        // Categorías Oficiales de Comercio CCT 130/75 — Escala Salarial Septiembre 2026 (FAECyS y CAC)
        $categoriesComercio = [
            // Maestranza
            ['code' => '001', 'name' => 'MAESTRANZA A', 'condition' => 'M', 'basic' => 1183900.00, 'non_taxable' => 120000.00],
            ['code' => '002', 'name' => 'MAESTRANZA B', 'condition' => 'M', 'basic' => 1187292.00, 'non_taxable' => 120000.00],
            ['code' => '003', 'name' => 'MAESTRANZA C', 'condition' => 'M', 'basic' => 1199176.00, 'non_taxable' => 120000.00],

            // Administrativos
            ['code' => '004', 'name' => 'ADMINISTRATIVO A', 'condition' => 'M', 'basic' => 1196632.00, 'non_taxable' => 120000.00],
            ['code' => '005', 'name' => 'ADMINISTRATIVO B', 'condition' => 'M', 'basic' => 1201729.00, 'non_taxable' => 120000.00],
            ['code' => '006', 'name' => 'ADMINISTRATIVO C', 'condition' => 'M', 'basic' => 1206821.00, 'non_taxable' => 120000.00],
            ['code' => '007', 'name' => 'ADMINISTRATIVO D', 'condition' => 'M', 'basic' => 1222103.00, 'non_taxable' => 120000.00],
            ['code' => '008', 'name' => 'ADMINISTRATIVO E', 'condition' => 'M', 'basic' => 1234836.00, 'non_taxable' => 120000.00],
            ['code' => '009', 'name' => 'ADMINISTRATIVO F', 'condition' => 'M', 'basic' => 1253514.00, 'non_taxable' => 120000.00],

            // Cajeros
            ['code' => '010', 'name' => 'CAJERO A', 'condition' => 'M', 'basic' => 1200875.00, 'non_taxable' => 120000.00],
            ['code' => '011', 'name' => 'CAJERO B', 'condition' => 'M', 'basic' => 1206821.00, 'non_taxable' => 120000.00],
            ['code' => '012', 'name' => 'CAJERO C', 'condition' => 'M', 'basic' => 1214462.00, 'non_taxable' => 120000.00],

            // Personal Auxiliar
            ['code' => '013', 'name' => 'PERSONAL AUXILIAR A', 'condition' => 'M', 'basic' => 1200875.00, 'non_taxable' => 120000.00],
            ['code' => '014', 'name' => 'PERSONAL AUXILIAR B', 'condition' => 'M', 'basic' => 1209365.00, 'non_taxable' => 120000.00],
            ['code' => '015', 'name' => 'PERSONAL AUXILIAR C', 'condition' => 'M', 'basic' => 1237383.00, 'non_taxable' => 120000.00],

            // Auxiliar Especializado
            ['code' => '016', 'name' => 'AUXILIAR ESPECIALIZADO A', 'condition' => 'M', 'basic' => 1211067.00, 'non_taxable' => 120000.00],
            ['code' => '017', 'name' => 'AUXILIAR ESPECIALIZADO B', 'condition' => 'M', 'basic' => 1226346.00, 'non_taxable' => 120000.00],

            // Vendedores
            ['code' => '018', 'name' => 'VENDEDOR A', 'condition' => 'M', 'basic' => 1200875.00, 'non_taxable' => 120000.00],
            ['code' => '019', 'name' => 'VENDEDOR B', 'condition' => 'M', 'basic' => 1226349.00, 'non_taxable' => 120000.00],
            ['code' => '020', 'name' => 'VENDEDOR C', 'condition' => 'M', 'basic' => 1234836.00, 'non_taxable' => 120000.00],
            ['code' => '021', 'name' => 'VENDEDOR D', 'condition' => 'M', 'basic' => 1253514.00, 'non_taxable' => 120000.00],
        ];

        foreach ($categoriesComercio as $catData) {
            $cat = AgreementCategory::updateOrCreate(
                ['agreement_id' => $cct003->id, 'code' => $catData['code']],
                [
                    'name' => $catData['name'],
                    'condition' => $catData['condition'],
                    'status' => 'ACTIVE',
                ]
            );

            // Escala vigente oficial Septiembre 2026 (Inc. NR $100.000 + Recomp. NR $20.000)
            CategoryScaleVersion::updateOrCreate(
                [
                    'agreement_category_id' => $cat->id,
                    'effective_from' => '2026-09-01',
                ],
                [
                    'effective_to' => null,
                    'basic_salary' => $catData['basic'],
                    'non_taxable_salary' => $catData['non_taxable'],
                    'notes' => 'FAECyS y CAC — Escala Septiembre 2026 (Inc. NR $100.000 + Recomp. NR $20.000)',
                    'created_by' => $admin?->id,
                ]
            );
        }

        // Categoría Fuera de Convenio
        $catFdc = AgreementCategory::firstOrCreate(
            ['agreement_id' => $cct001->id, 'code' => '001'],
            [
                'name' => 'DIRECTOR / GERENTE',
                'condition' => 'M',
                'status' => 'ACTIVE',
            ]
        );

        CategoryScaleVersion::firstOrCreate(
            [
                'agreement_category_id' => $catFdc->id,
                'effective_from' => '2026-01-01',
            ],
            [
                'effective_to' => null,
                'basic_salary' => 2500000.00,
                'non_taxable_salary' => 0.00,
                'notes' => 'Acuerdo particular de partes',
                'created_by' => $admin?->id,
            ]
        );
    }
}

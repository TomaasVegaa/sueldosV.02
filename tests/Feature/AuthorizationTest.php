<?php

namespace Tests\Feature;

use App\Domain\Tenant\EmployerContext;
use App\Livewire\Employers\EmployerIndex;
use App\Models\Employer;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    public function test_role_permission_matrix_is_seeded(): void
    {
        $this->assertGreaterThan(0, DB::table('role_permissions')->count());

        $liquidador = User::where('email', 'operador1@estudio.test')->firstOrFail();
        $this->assertTrue($liquidador->hasAnyPermission('payroll.calculate'));
        $this->assertFalse($liquidador->hasAnyPermission('payroll.approve'));
        $this->assertFalse($liquidador->hasAnyPermission('audit.view'));
    }

    public function test_liquidador_cannot_open_audit_log(): void
    {
        $liquidador = User::where('email', 'operador1@estudio.test')->firstOrFail();

        $this->actingAs($liquidador)
            ->get('/audit')
            ->assertForbidden();
    }

    public function test_auditor_can_view_but_cannot_create_employers(): void
    {
        $auditor = $this->createUserWithRole('auditor@estudio.test', 'AUDITOR');

        Livewire::actingAs($auditor)
            ->test(EmployerIndex::class)
            ->assertOk()
            ->call('openCreateModal')
            ->assertForbidden();
    }

    public function test_employer_scoped_operator_cannot_switch_to_another_employer(): void
    {
        $employers = Employer::orderBy('id')->get();
        $allowedEmployer = $employers->firstOrFail();
        $forbiddenEmployer = $employers->skip(1)->firstOrFail();
        $operator = $this->createUserWithRole(
            'empresa-limitada@estudio.test',
            'LIQUIDADOR',
            $allowedEmployer->id
        );

        $this->actingAs($operator);
        EmployerContext::setActiveEmployerId($allowedEmployer->id);

        Livewire::actingAs($operator)
            ->test(EmployerIndex::class)
            ->assertSee($allowedEmployer->legal_name)
            ->assertDontSee($forbiddenEmployer->legal_name)
            ->call('activateEmployer', $forbiddenEmployer->id)
            ->assertForbidden();
    }

    private function createUserWithRole(string $email, string $roleCode, ?int $employerId = null): User
    {
        $user = User::factory()->create([
            'email' => $email,
            'status' => 'ACTIVE',
        ]);
        $role = Role::where('code', $roleCode)->firstOrFail();
        $user->roles()->attach($role->id, ['employer_id' => $employerId]);

        return $user;
    }
}

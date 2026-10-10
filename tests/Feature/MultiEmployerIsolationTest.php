<?php

namespace Tests\Feature;

use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\Employer;
use App\Models\Employment;
use App\Models\Role;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Auth\Access\AuthorizationException;
use Tests\TestCase;

class MultiEmployerIsolationTest extends TestCase
{
    public function test_multi_employer_scope_filters_records_by_active_employer(): void
    {
        $this->actingAs(User::where('email', 'admin@estudio.test')->firstOrFail());

        $emp1 = Employer::where('cuit', '30718096045')->first();
        $emp2 = Employer::where('cuit', '30654321098')->first();

        $this->assertNotNull($emp1);
        $this->assertNotNull($emp2);

        // When Employer 1 is active
        EmployerContext::setActiveEmployerId($emp1->id);
        $employmentsEmp1 = Employment::all();
        foreach ($employmentsEmp1 as $emp) {
            $this->assertEquals($emp1->id, $emp->employer_id);
        }

        // When Employer 2 is active
        EmployerContext::setActiveEmployerId($emp2->id);
        $employmentsEmp2 = Employment::all();
        foreach ($employmentsEmp2 as $emp) {
            $this->assertEquals($emp2->id, $emp->employer_id);
        }

        // Clean up context
        EmployerContext::setActiveEmployerId(null);
    }

    public function test_cuit_modulo_11_validation(): void
    {
        // Valid CUITs
        $this->assertTrue(Employer::isValidCuit('30718096045'));
        $this->assertTrue(Employer::isValidCuit('30-71809604-5'));
        $this->assertTrue(Worker::isValidCuil('20351234564'));

        // Invalid CUITs
        $this->assertFalse(Employer::isValidCuit('30718096040')); // Wrong check digit
        $this->assertFalse(Employer::isValidCuit('12345'));        // Too short
        $this->assertFalse(Employer::isValidCuit('abcdefghijk'));  // Non-digits
    }

    public function test_audit_event_logging(): void
    {
        $user = User::first();
        $emp = Employer::first();

        $this->actingAs($user);
        EmployerContext::setActiveEmployerId($emp->id);

        $event = AuditEvent::record(
            action: 'CREATE',
            entityType: 'Worker',
            entityId: 999,
            newValues: ['name' => 'Test Worker'],
            reason: 'Alta de empleado en test'
        );

        $this->assertDatabaseHas('audit_events', [
            'id' => $event->id,
            'user_id' => $user->id,
            'employer_id' => $emp->id,
            'action' => 'CREATE',
            'entity_type' => 'Worker',
        ]);
    }

    public function test_effective_version_resolution(): void
    {
        $employment = Employment::withoutGlobalScopes()->first();
        $this->assertNotNull($employment);

        $this->actingAs(User::where('email', 'admin@estudio.test')->firstOrFail());
        EmployerContext::setActiveEmployerId($employment->employer_id);

        $version = $employment->getVersionOn('2026-02-01');
        $this->assertNotNull($version);
        $this->assertEquals('003', $version->agreement_code);
    }

    public function test_operator_has_access_to_active_employers(): void
    {
        $operator = User::where('email', 'operador1@estudio.test')->first();
        $this->assertNotNull($operator);
        $employers = $operator->accessibleEmployers();
        $this->assertGreaterThanOrEqual(2, $employers->count());
    }

    public function test_tenant_models_fail_closed_without_authenticated_context(): void
    {
        $this->assertSame(0, Employment::count());
    }

    public function test_operator_cannot_activate_an_unassigned_employer(): void
    {
        $employers = Employer::orderBy('id')->get();
        $allowedEmployer = $employers->firstOrFail();
        $forbiddenEmployer = $employers->skip(1)->firstOrFail();

        $operator = User::factory()->create([
            'email' => 'limitado@estudio.test',
            'status' => 'ACTIVE',
        ]);
        $role = Role::where('code', 'LIQUIDADOR')->firstOrFail();
        $operator->roles()->attach($role->id, ['employer_id' => $allowedEmployer->id]);

        $this->actingAs($operator);
        EmployerContext::setActiveEmployerId($allowedEmployer->id);
        $this->assertSame($allowedEmployer->id, EmployerContext::getActiveEmployerId());

        $this->expectException(AuthorizationException::class);
        EmployerContext::setActiveEmployerId($forbiddenEmployer->id);
    }
}

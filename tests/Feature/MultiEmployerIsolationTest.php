<?php

namespace Tests\Feature;

use App\Domain\Tenant\EmployerContext;
use App\Models\AuditEvent;
use App\Models\Employer;
use App\Models\Employment;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiEmployerIsolationTest extends TestCase
{
    // Do not use RefreshDatabase on MySQL directly so we keep seeded database,
    // or test against SQLite in-memory if available. Let's inspect test environment.
    
    public function test_multi_employer_scope_filters_records_by_active_employer(): void
    {
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
}

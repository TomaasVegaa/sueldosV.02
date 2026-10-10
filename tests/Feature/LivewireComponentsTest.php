<?php

namespace Tests\Feature;

use App\Domain\Tenant\EmployerContext;
use App\Livewire\Arca\CatalogViewer;
use App\Livewire\Audit\AuditLogViewer;
use App\Livewire\Employers\EmployerIndex;
use App\Livewire\HeaderEmployerSelector;
use App\Livewire\Workforce\EmploymentIndex;
use App\Models\Employer;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireComponentsTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'admin@estudio.test')->first();
        $this->actingAs($this->user);
    }

    public function test_header_employer_selector_renders(): void
    {
        $employer = Employer::first();
        EmployerContext::setActiveEmployerId($employer->id);

        Livewire::test(HeaderEmployerSelector::class)
            ->assertSee($employer->legal_name)
            ->assertStatus(200);
    }

    public function test_employer_index_renders_and_lists_employers(): void
    {
        Livewire::test(EmployerIndex::class)
            ->assertSee('Empresas Clientes')
            ->assertSee('EMPRESA COMERCIAL NORTE S.A.')
            ->assertStatus(200);
    }

    public function test_workforce_index_renders_scoped_employees(): void
    {
        $emp1 = Employer::where('cuit', '30718096045')->first();
        EmployerContext::setActiveEmployerId($emp1->id);

        Livewire::test(EmploymentIndex::class)
            ->assertSee($emp1->legal_name)
            ->assertSee('Lucas')
            ->assertSee('Rodríguez')
            ->assertStatus(200);
    }

    public function test_catalog_viewer_renders_and_searches_obras_sociales(): void
    {
        Livewire::test(CatalogViewer::class)
            ->assertSee('Catálogos Oficiales ARCA')
            ->set('search', '126205')
            ->assertSee('126205')
            ->assertSee('COMERCIO')
            ->assertStatus(200);
    }

    public function test_audit_log_viewer_renders(): void
    {
        Livewire::test(AuditLogViewer::class)
            ->assertSee('Registro de Auditoría y Trazabilidad')
            ->assertStatus(200);
    }

    public function test_agreements_index_renders_and_lists_categories_with_scales(): void
    {
        Livewire::test(\App\Livewire\Agreements\AgreementIndex::class)
            ->assertSee('Convenios, Escalas y Fórmulas')
            ->assertSee('Comercio — CCT 130/75')
            ->assertSee('VENDEDOR B')
            ->assertSee('1.226.349,00')
            ->assertSee('120.000,00')
            ->set('activeTab', 'CONCEPTS')
            ->assertSee('Conceptos y Fórmulas')
            ->assertSee('SUELDO BASICO')
            ->assertSee('SB * 0.01 * AN')
            ->assertStatus(200);
    }

    public function test_workforce_modal_calculates_scale_and_prorated_salary(): void
    {
        $emp1 = Employer::where('cuit', '30718096045')->first();
        EmployerContext::setActiveEmployerId($emp1->id);

        $test = Livewire::test(EmploymentIndex::class)
            ->set('agreement_code', '003')
            ->set('category_code', '019')
            ->set('workday_type', 1);

        $this->assertEquals(1226349.00, $test->get('scale_basic'));
        $this->assertEquals(120000.00, $test->get('scale_non_taxable'));
        $this->assertEquals(1226349.00, $test->get('suggested_basic'));

        // Test half workday (50%)
        $test->set('workday_type', 3);
        $this->assertEquals(613174.50, $test->get('suggested_basic'));

        // Test quarter workday (25%)
        $test->set('workday_type', 4);
        $this->assertEquals(306587.25, $test->get('suggested_basic'));
    }
}

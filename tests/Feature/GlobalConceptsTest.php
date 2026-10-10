<?php

namespace Tests\Feature;

use App\Domain\Payroll\Engine\ExpressionEvaluator;
use App\Livewire\Concepts\ConceptIndex;
use App\Models\PayrollConcept;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class GlobalConceptsTest extends TestCase
{
    /** @test */
    public function global_concepts_page_can_be_rendered(): void
    {
        $user = User::first();
        $this->actingAs($user);

        $response = $this->get('/concepts');
        $response->assertStatus(200);
        $response->assertSee('Catálogo Global de Conceptos');
    }

    /** @test */
    public function concept_index_component_lists_global_concepts(): void
    {
        $user = User::first();
        $this->actingAs($user);

        Livewire::test(ConceptIndex::class)
            ->assertSee('SUELDO BASICO')
            ->assertSee('001')
            ->assertSee('PRESENTISMO')
            ->assertSee('Transversal a todos los convenios')
            ->set('typeFilter', 'DESCUENTO')
            ->assertSee('JUBILACION')
            ->assertSee('400');
    }

    /** @test */
    public function concept_can_be_created_and_applies_globally(): void
    {
        $user = User::first();
        $this->actingAs($user);

        Livewire::test(ConceptIndex::class)
            ->call('openCreateModal')
            ->set('code', '999')
            ->set('name', 'BONO EXTRAORDINARIO ESTUDIO')
            ->set('type', 'NO_REMUNERATIVO')
            ->set('formula', '50000 * VI')
            ->set('calculation_mode', 'FORMULA')
            ->set('order', 999)
            ->call('saveConcept')
            ->assertHasNoErrors();

        $concept = PayrollConcept::where('code', '999')->first();
        $this->assertNotNull($concept);
        $this->assertNull($concept->agreement_id);
        $this->assertNull($concept->employer_id);
        $this->assertTrue($concept->is_global);
    }

    /** @test */
    public function formula_tester_evaluates_exact_amount(): void
    {
        $user = User::first();
        $this->actingAs($user);

        Livewire::test(ConceptIndex::class)
            ->call('openCreateModal')
            ->set('formula', 'SB * 0.01 * AN')
            ->set('test_sb', '600000.00')
            ->set('test_an', '5')
            ->call('testCurrentFormula')
            ->assertSet('test_result', '$ 30.000,00');
    }
}

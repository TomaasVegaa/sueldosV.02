<?php

namespace Tests\Unit;

use App\Domain\Payroll\Engine\DependencyGraph;
use App\Domain\Payroll\Engine\ExpressionEvaluator;
use App\Domain\Payroll\Engine\Lexer;
use App\Domain\Payroll\Engine\Parser;
use App\Domain\Payroll\Engine\Token;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PayrollEngineTest extends TestCase
{
    public function test_lexer_tokenizes_formula_correctly(): void
    {
        $lexer = new Lexer('(SB + LQ002) * 8.3333 / 100');
        $tokens = $lexer->tokenize();

        $types = array_map(fn (Token $t) => $t->type, $tokens);
        $expected = [
            Token::T_LPAREN,
            Token::T_IDENTIFIER,
            Token::T_PLUS,
            Token::T_IDENTIFIER,
            Token::T_RPAREN,
            Token::T_MULTIPLY,
            Token::T_NUMBER,
            Token::T_DIVIDE,
            Token::T_NUMBER,
            Token::T_EOF,
        ];

        $this->assertEquals($expected, $types);
    }

    public function test_parser_enforces_operator_precedence(): void
    {
        // 10 + 20 * 2 should be 50, not 60
        $result = ExpressionEvaluator::evaluateRounded('10 + 20 * 2');
        $this->assertEquals('50.00', $result);

        // (10 + 20) * 2 should be 60
        $resultWithParens = ExpressionEvaluator::evaluateRounded('(10 + 20) * 2');
        $this->assertEquals('60.00', $resultWithParens);
    }

    public function test_evaluator_evaluates_exact_comercio_basic_and_presentismo(): void
    {
        $context = [
            'SB' => '1226349.00',
            'AN' => 3, // 3 años de antigüedad
        ];

        // 1. Antigüedad (1% por año): SB * AN / 100
        $antiguedad = ExpressionEvaluator::evaluateRounded('SB * AN / 100', $context);
        $this->assertEquals('36790.47', $antiguedad);

        // 2. Presentismo: (SB + LQ002) * 8.3333 / 100
        $context['LQ002'] = $antiguedad;
        $presentismo = ExpressionEvaluator::evaluateRounded('(SB + LQ002) * 8.3333 / 100', $context);
        $this->assertEquals('105261.20', $presentismo);

        // 3. Total Haberes Remunerativos (TH)
        $th = (float) $context['SB'] + (float) $antiguedad + (float) $presentismo;
        $context['TH'] = (string) $th;

        // 4. Jubilación 11%: TH * 11 / 100
        $jubilacion = ExpressionEvaluator::evaluateRounded('TH * 11 / 100', $context);
        $this->assertEquals('150524.07', $jubilacion);

        // 5. Obra Social 3%: TH * 3 / 100
        $obraSocial = ExpressionEvaluator::evaluateRounded('TH * 3 / 100', $context);
        $this->assertEquals('41052.02', $obraSocial);
    }

    public function test_dependency_graph_resolves_correct_topological_order(): void
    {
        $graph = new DependencyGraph();

        // Add out of order
        $graph->addConcept('201', 'TH * 11 / 100', 'DESCUENTO');
        $graph->addConcept('004', '(SB + LQ002) * 8.3333 / 100', 'REMUNERATIVO');
        $graph->addConcept('002', 'SB * AN / 100', 'REMUNERATIVO');
        $graph->addConcept('001', 'SB', 'REMUNERATIVO');

        $order = $graph->getCalculationOrder();

        // 001 and 002 must come before 004, and 004 must come before 201 (since 201 uses TH)
        $pos001 = array_search('001', $order);
        $pos002 = array_search('002', $order);
        $pos004 = array_search('004', $order);
        $pos201 = array_search('201', $order);

        $this->assertLessThan($pos004, $pos002, 'Antigüedad 002 must be calculated before Presentismo 004');
        $this->assertLessThan($pos201, $pos004, 'Presentismo 004 must be calculated before Jubilación 201');
    }

    public function test_dependency_graph_detects_circular_dependency(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Dependencia circular');

        $graph = new DependencyGraph();
        $graph->addConcept('001', 'SB + LQ002', 'REMUNERATIVO');
        $graph->addConcept('002', 'LQ001 * 0.10', 'REMUNERATIVO');

        $graph->getCalculationOrder();
    }
}

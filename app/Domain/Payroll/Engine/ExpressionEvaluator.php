<?php

namespace App\Domain\Payroll\Engine;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

class ExpressionEvaluator
{
    /** Scale used for intermediate division operations */
    public const INTERMEDIATE_SCALE = 8;

    /** Scale used for final monetary rounding (cents) */
    public const FINAL_SCALE = 2;

    /**
     * Evaluate an AST or expression string with the given variable context.
     *
     * @param  array<string, mixed>  $context
     */
    public static function evaluate(AstNode|string $expression, array $context = []): BigDecimal
    {
        $node = is_string($expression) ? Parser::parseString($expression) : $expression;
        $evaluator = new self($context);

        return $evaluator->evaluateNode($node);
    }

    /**
     * Helper to evaluate and return a rounded two-decimal string.
     */
    public static function evaluateRounded(AstNode|string $expression, array $context = []): string
    {
        $result = self::evaluate($expression, $context);

        return (string) $result->toScale(self::FINAL_SCALE, RoundingMode::HALF_UP);
    }

    /** @var array<string, BigDecimal> */
    protected array $normalizedContext = [];

    public function __construct(array $context = [])
    {
        foreach ($context as $key => $value) {
            $normalizedKey = strtoupper(trim((string) $key));
            if ($value instanceof BigDecimal) {
                $this->normalizedContext[$normalizedKey] = $value;
            } elseif (is_int($value) || (is_string($value) && is_numeric($value))) {
                $this->normalizedContext[$normalizedKey] = BigDecimal::of((string) $value);
            } else {
                throw new InvalidArgumentException("Valor no decimal para la variable {$normalizedKey}.");
            }
        }
    }

    public function evaluateNode(AstNode $node): BigDecimal
    {
        if ($node instanceof NumberNode) {
            return BigDecimal::of($node->value);
        }

        if ($node instanceof VariableNode) {
            $varName = strtoupper($node->name);
            if (! array_key_exists($varName, $this->normalizedContext)) {
                throw new InvalidArgumentException("Variable desconocida en la fórmula: {$varName}.");
            }

            return $this->normalizedContext[$varName];
        }

        if ($node instanceof UnaryOpNode) {
            $operandVal = $this->evaluateNode($node->operand);

            return $node->operator === '-' ? $operandVal->negated() : $operandVal;
        }

        if ($node instanceof BinaryOpNode) {
            $leftVal = $this->evaluateNode($node->left);
            $rightVal = $this->evaluateNode($node->right);

            return match ($node->operator) {
                '+' => $leftVal->plus($rightVal),
                '-' => $leftVal->minus($rightVal),
                '*' => $leftVal->multipliedBy($rightVal),
                '/' => $this->divide($leftVal, $rightVal),
                default => throw new InvalidArgumentException("Operador binario no soportado: '{$node->operator}'"),
            };
        }

        throw new InvalidArgumentException('Tipo de nodo AST no reconocido: '.get_class($node));
    }

    protected function divide(BigDecimal $left, BigDecimal $right): BigDecimal
    {
        if ($right->isZero()) {
            throw new InvalidArgumentException('División por cero en el cálculo salarial.');
        }

        return $left->dividedBy($right, self::INTERMEDIATE_SCALE, RoundingMode::HALF_UP);
    }
}

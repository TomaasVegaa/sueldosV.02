<?php

namespace App\Domain\Payroll\Engine;

class UnaryOpNode extends AstNode
{
    public function __construct(
        public readonly string $operator,
        public readonly AstNode $operand
    ) {}

    public function getReferencedVariables(): array
    {
        return $this->operand->getReferencedVariables();
    }
}

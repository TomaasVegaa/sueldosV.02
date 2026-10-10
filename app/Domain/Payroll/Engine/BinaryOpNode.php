<?php

namespace App\Domain\Payroll\Engine;

class BinaryOpNode extends AstNode
{
    public function __construct(
        public readonly string $operator,
        public readonly AstNode $left,
        public readonly AstNode $right
    ) {}

    public function getReferencedVariables(): array
    {
        return array_unique(array_merge(
            $this->left->getReferencedVariables(),
            $this->right->getReferencedVariables()
        ));
    }
}

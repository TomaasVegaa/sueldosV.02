<?php

namespace App\Domain\Payroll\Engine;

class VariableNode extends AstNode
{
    public function __construct(public readonly string $name) {}

    public function getReferencedVariables(): array
    {
        return [$this->name];
    }
}

<?php

namespace App\Domain\Payroll\Engine;

class NumberNode extends AstNode
{
    public function __construct(public readonly string $value) {}

    public function getReferencedVariables(): array
    {
        return [];
    }
}

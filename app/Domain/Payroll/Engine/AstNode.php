<?php

namespace App\Domain\Payroll\Engine;

abstract class AstNode
{
    /**
     * @return array<string>
     */
    abstract public function getReferencedVariables(): array;
}

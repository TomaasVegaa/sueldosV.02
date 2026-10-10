<?php

namespace App\Domain\Payroll\Engine;

class Token
{
    public const T_NUMBER = 'NUMBER';
    public const T_IDENTIFIER = 'IDENTIFIER';
    public const T_PLUS = '+';
    public const T_MINUS = '-';
    public const T_MULTIPLY = '*';
    public const T_DIVIDE = '/';
    public const T_LPAREN = '(';
    public const T_RPAREN = ')';
    public const T_EOF = 'EOF';

    public function __construct(
        public readonly string $type,
        public readonly string $value,
        public readonly int $position
    ) {}

    public function is(string $type): bool
    {
        return $this->type === $type;
    }
}

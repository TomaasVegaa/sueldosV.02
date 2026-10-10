<?php

namespace App\Domain\Payroll\Engine;

use InvalidArgumentException;

class Parser
{
    /** @var Token[] */
    protected array $tokens;

    protected int $cursor = 0;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public static function parseString(string $expression): AstNode
    {
        $lexer = new Lexer($expression);
        $tokens = $lexer->tokenize();
        $parser = new self($tokens);

        return $parser->parse();
    }

    public function parse(): AstNode
    {
        $node = $this->parseExpression();

        if (! $this->currentToken()->is(Token::T_EOF)) {
            throw new InvalidArgumentException(
                "Sintaxis inválida cerca del token '{$this->currentToken()->value}' en posición {$this->currentToken()->position}"
            );
        }

        return $node;
    }

    /**
     * Expression: Term (('+' | '-') Term)*
     */
    protected function parseExpression(): AstNode
    {
        $left = $this->parseTerm();

        while ($this->currentToken()->is(Token::T_PLUS) || $this->currentToken()->is(Token::T_MINUS)) {
            $op = $this->currentToken()->value;
            $this->advance();
            $right = $this->parseTerm();
            $left = new BinaryOpNode($op, $left, $right);
        }

        return $left;
    }

    /**
     * Term: Factor (('*' | '/') Factor)*
     */
    protected function parseTerm(): AstNode
    {
        $left = $this->parseFactor();

        while ($this->currentToken()->is(Token::T_MULTIPLY) || $this->currentToken()->is(Token::T_DIVIDE)) {
            $op = $this->currentToken()->value;
            $this->advance();
            $right = $this->parseFactor();
            $left = new BinaryOpNode($op, $left, $right);
        }

        return $left;
    }

    /**
     * Factor: ('+' | '-')? (Number | Variable | '(' Expression ')')
     */
    protected function parseFactor(): AstNode
    {
        $token = $this->currentToken();

        // Unary plus
        if ($token->is(Token::T_PLUS)) {
            $this->advance();

            return $this->parseFactor();
        }

        // Unary minus
        if ($token->is(Token::T_MINUS)) {
            $this->advance();
            $operand = $this->parseFactor();

            return new UnaryOpNode('-', $operand);
        }

        // Parentheses
        if ($token->is(Token::T_LPAREN)) {
            $this->advance();
            $expr = $this->parseExpression();

            if (! $this->currentToken()->is(Token::T_RPAREN)) {
                throw new InvalidArgumentException("Se esperaba paréntesis de cierre ')' en posición {$this->currentToken()->position}");
            }

            $this->advance();

            return $expr;
        }

        // Number literal
        if ($token->is(Token::T_NUMBER)) {
            $value = $token->value;
            $this->advance();

            return new NumberNode($value);
        }

        // Variable / Identifier
        if ($token->is(Token::T_IDENTIFIER)) {
            $name = $token->value;
            $this->advance();

            return new VariableNode($name);
        }

        throw new InvalidArgumentException(
            "Se esperaba un número, variable o paréntesis en posición {$token->position}, se encontró '{$token->value}'"
        );
    }

    protected function currentToken(): Token
    {
        return $this->tokens[$this->cursor];
    }

    protected function advance(): void
    {
        if ($this->cursor < count($this->tokens) - 1) {
            $this->cursor++;
        }
    }
}

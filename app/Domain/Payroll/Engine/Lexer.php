<?php

namespace App\Domain\Payroll\Engine;

use InvalidArgumentException;

class Lexer
{
    protected string $input;

    protected int $length;

    protected int $cursor = 0;

    public function __construct(string $input)
    {
        $this->input = trim($input);
        $this->length = strlen($this->input);
    }

    /**
     * Tokenize the entire input into an array of Tokens ending with EOF.
     *
     * @return Token[]
     */
    public function tokenize(): array
    {
        $tokens = [];

        while ($this->cursor < $this->length) {
            $char = $this->input[$this->cursor];

            // Ignore whitespace
            if (ctype_space($char)) {
                $this->cursor++;

                continue;
            }

            // Numbers (integers and decimals)
            if (ctype_digit($char) || ($char === '.' && $this->cursor + 1 < $this->length && ctype_digit($this->input[$this->cursor + 1]))) {
                $start = $this->cursor;
                $hasDot = false;

                while ($this->cursor < $this->length) {
                    $c = $this->input[$this->cursor];
                    if (ctype_digit($c)) {
                        $this->cursor++;
                    } elseif ($c === '.' && ! $hasDot) {
                        $hasDot = true;
                        $this->cursor++;
                    } else {
                        break;
                    }
                }

                $value = substr($this->input, $start, $this->cursor - $start);
                $tokens[] = new Token(Token::T_NUMBER, $value, $start);

                continue;
            }

            // Identifiers / variables (SB, NR, LQ002, TH, etc.)
            if (ctype_alpha($char) || $char === '_') {
                $start = $this->cursor;

                while ($this->cursor < $this->length && (ctype_alnum($this->input[$this->cursor]) || $this->input[$this->cursor] === '_')) {
                    $this->cursor++;
                }

                $value = strtoupper(substr($this->input, $start, $this->cursor - $start));
                $tokens[] = new Token(Token::T_IDENTIFIER, $value, $start);

                continue;
            }

            // Single character operators and delimiters
            $start = $this->cursor;
            switch ($char) {
                case '+':
                    $tokens[] = new Token(Token::T_PLUS, '+', $start);
                    $this->cursor++;
                    break;
                case '-':
                    $tokens[] = new Token(Token::T_MINUS, '-', $start);
                    $this->cursor++;
                    break;
                case '*':
                    $tokens[] = new Token(Token::T_MULTIPLY, '*', $start);
                    $this->cursor++;
                    break;
                case '/':
                    $tokens[] = new Token(Token::T_DIVIDE, '/', $start);
                    $this->cursor++;
                    break;
                case '(':
                    $tokens[] = new Token(Token::T_LPAREN, '(', $start);
                    $this->cursor++;
                    break;
                case ')':
                    $tokens[] = new Token(Token::T_RPAREN, ')', $start);
                    $this->cursor++;
                    break;
                default:
                    throw new InvalidArgumentException("Carácter inesperado '{$char}' en posición {$this->cursor} de la fórmula: '{$this->input}'");
            }
        }

        $tokens[] = new Token(Token::T_EOF, '', $this->cursor);

        return $tokens;
    }
}

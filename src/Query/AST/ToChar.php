<?php

namespace App\Query\AST;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Psr\Log\LoggerInterface;

class ToChar extends FunctionNode
{
    public $timestamp = null;
    public $timezone = null;
    public $pattern = null;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'to_char('.$this->timestamp->dispatch($sqlWalker) . ' at time zone  '
            .$this->timezone->dispatch($sqlWalker).' , ' . $this->pattern->dispatch($sqlWalker) . ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->timestamp = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->timezone = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->pattern = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

}

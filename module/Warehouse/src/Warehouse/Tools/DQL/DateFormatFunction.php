<?php
namespace Warehouse\Tools\DQL;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * Adds the hability to use the MySQL DATE_FORMAT function inside Doctrine
 */
class DateFormatFunction extends FunctionNode
{
    protected $dateExpression;
    protected $formatChar;

    public function getSql( SqlWalker $sqlWalker )
    {
        return 'DATE_FORMAT(' . $sqlWalker->walkArithmeticExpression( $this->dateExpression ) . ',' . $sqlWalker->walkStringPrimary( $this->formatChar ) . ')';
    }

    public function parse( Parser $parser )
    {
        $parser->Match( Lexer::T_IDENTIFIER );
        $parser->Match( Lexer::T_OPEN_PARENTHESIS );

        $this->dateExpression = $parser->ArithmeticExpression();
        $parser->Match( Lexer::T_COMMA );

        $this->formatChar = $parser->ArithmeticExpression();

        $parser->Match( Lexer::T_CLOSE_PARENTHESIS );
    }
}

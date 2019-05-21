<?php declare(strict_types = 1);

namespace Apicart\FQL\Contract\Parser;

use Apicart\FQL\Value\SyntaxTree;
use Apicart\FQL\Value\TokenSequence;

interface ParserInterface
{

    public function parse(TokenSequence $tokenSequence): SyntaxTree;

}

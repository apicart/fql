<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Integration;

use Apicart\FQL\Generator\Common\Aggregate;
use Apicart\FQL\Generator\SQL\BinaryOperator;
use Apicart\FQL\Generator\SQL\Group;
use Apicart\FQL\Generator\SQL\Phrase;
use Apicart\FQL\Generator\SQL\Query;
use Apicart\FQL\Generator\SQL\Range;
use Apicart\FQL\Generator\SQL\Resolver\AbstractFilterResolver;
use Apicart\FQL\Generator\SQL\UnaryOperator;
use Apicart\FQL\Generator\SQL\Word;
use Apicart\FQL\Tokenizer\Full;
use Apicart\FQL\Tokenizer\Parser;
use Apicart\FQL\Tokenizer\Tokenizer;

final class FilterParser
{

    public static function parse(string $fql, AbstractFilterResolver $filterResolver): string
    {
        $tokenExtractor = new Full;
        $tokenizer = new Tokenizer($tokenExtractor);
        $tokenSequence = $tokenizer->tokenize($fql);

        $parser = new Parser;
        $syntaxTree = $parser->parse($tokenSequence);

        $visitor = new Aggregate(
            [
                new BinaryOperator,
                new UnaryOperator,
                new Group,
                new Query,
                new Phrase($filterResolver),
                new Range($filterResolver),
                new Word($filterResolver),
            ]
        );

        return $visitor->visit($syntaxTree->getRootNode());
    }

}

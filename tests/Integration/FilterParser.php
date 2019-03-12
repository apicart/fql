<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Integration;

use Apicart\FQL\Generator\Common\Aggregate;
use Apicart\FQL\Tests\Integration\Generator\SQL\Resolver\AbstractFilterResolver;
use Apicart\FQL\Tests\Integration\Generator\SQL\Visitor\Binary;
use Apicart\FQL\Tests\Integration\Generator\SQL\Visitor\Group;
use Apicart\FQL\Tests\Integration\Generator\SQL\Visitor\Phrase;
use Apicart\FQL\Tests\Integration\Generator\SQL\Visitor\Query;
use Apicart\FQL\Tests\Integration\Generator\SQL\Visitor\Range;
use Apicart\FQL\Tests\Integration\Generator\SQL\Visitor\Word;
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
				new Binary,
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

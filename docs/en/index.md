# Introduction

Write filter query as simple string via Filter Query Language (FQL) syntax. Filter query will be parsed into easy-to-use syntax tree.

**Simple FQL example:**

`q:"samsung" AND introducedAt:["2019-01-01 00:00:00" TO NOW] AND type:(tv OR "mobile phone")`


## Syntax

FQL is based on a syntax that seems to be the unofficial standard for search query as user input. It should feel familiar, as the same basic syntax is used by any popular text-based search engine out there. It is also very similar to Lucene Query Parser syntax, used by both Solr and Elasticsearch.

## Example

### FQL transformation

*Note: Code examples are taken from [tests](https://github.com/apicart/fql/tree/master/tests/Integration).*

Every filter query will operate under specific context eg. filter query for items or customers etc. So if I would like to create filter query resolver for FQL from "Introduction" section it could look like this:

```php
<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Integration\Generator\SQL\Resolver;

use Apicart\FQL\Token\Token\Range;
use DateTime;

final class ItemFilterResolver extends AbstractFilterResolver
{
	protected function getResolvers(): array
	{
		return [
			'q' => function (string $value) {
				return $this->queryResolver($value);
			},
			'introducedAt' => function (Range $range) {
				return $this->introducedAtResolver($range);
			},
			'type' => function (string $value) {
				return $this->typeResolver($value);
			},
		];
	}

	private function queryResolver(string $value): string
	{
		return "name ILIKE '%${value}%'";
	}

	private function introducedAtResolver(Range $range): string
	{
		$fromOperator = $range->getStartType() === Range::TYPE_INCLUSIVE ? '>=' : '>';
		$toOperator = $range->getEndType() === Range::TYPE_INCLUSIVE ? '<=' : '<';
		$rangeFrom = new DateTime($range->getRangeFrom());
		$rangeTo = new DateTime($range->getRangeTo());

		return sprintf(
			"introduced_at %s '%s' AND introduced_at %s '%s'",
			$fromOperator,
			$rangeFrom->format(DateTime::ATOM),
			$toOperator,
			$rangeTo->format(DateTime::ATOM)
		);
	}

	private function typeResolver(string $value): string
	{
		return "type = '${value}'";
	}
}
```

*Note: If you need join some tables for complex SQL you could use eg. `Doctrine\DBAL\Query\QueryBuilder` and pass it via constructor into your resolver. :)*

Then you need define your own query parser with allowed FQL tokens. Simple query parser supporting all FQL tokens could look like this:

```php
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
``` 

Finally FQL to SQL transformation process could look like this:

```php
<?php declare(strict_types = 1);

use Apicart\FQL\Tests\Integration\FilterParser;
use Apicart\FQL\Tests\Integration\Generator\SQL\Resolver\ItemFilterResolver;

$fql = 'q:"samsung" AND introducedAt:["2019-01-01 00:00:00" TO "2019-01-31 23:59:59"] AND type:(tv OR "mobile phone")';

$resolver = new ItemFilterResolver;
$sql = FilterParser::parse($fql, $resolver);

echo $sql; // "name ILIKE '%samsung%' AND introduced_at >= '2019-01-01T00:00:00+00:00' AND introduced_at <= '2019-01-31T23:59:59+00:00' AND (type = 'tv' OR type = 'mobile phone')"
```

For more informations about [token visitors](https://github.com/apicart/fql/tree/master/tests/Integration/Generator/SQL/Visitor), [fql resolvers](https://github.com/apicart/fql/tree/master/tests/Integration/Generator/SQL/Resolver) and [fql transformations](https://github.com/apicart/fql/tree/master/tests/Generator/SQL/FilterParserTest.php) see our [tests](https://github.com/apicart/fql/tree/master/tests).

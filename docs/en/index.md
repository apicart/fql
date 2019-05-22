# Introduction

Write filter query as simple string via Filter Query Language (FQL) syntax. Filter query will be parsed into easy-to-use syntax tree.

**Simple FQL example:**

`q:"samsung" AND introducedAt:["2019-01-01 00:00:00" TO NOW] AND NOT type:(tv OR "mobile phone") OR price:{10 TO *]`


## Syntax

FQL is based on a syntax that seems to be the unofficial standard for search query as user input. It should feel familiar, as the same basic syntax is used by any popular text-based search engine out there. It is also very similar to Lucene Query Parser syntax, used by both Solr and Elasticsearch.

## Example

### FQL transformation

*Note: Code examples are taken from [tests](https://github.com/apicart/fql/tree/master/tests/Integration).*

Every filter query will operate under specific context eg. filter query for items or customers etc. So if I would like to create filter query resolver for FQL from "Introduction" section it could look like this:

```php
<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Integration\Generator\SQL\Resolver;

use Apicart\FQL\Generator\SQL\Resolver\AbstractFilterResolver;
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
            'price' => function (Range $range) {
                return $this->priceResolver($range);
            },
        ];
    }

    private function queryResolver(string $value): string
    {
        return "name ILIKE '%${value}%'";
    }

    private function introducedAtResolver(Range $range): string
    {
        $rangeFrom = new DateTime($range->getStartValue());
        $rangeTo = new DateTime($range->getEndValue());

        return sprintf(
            "introduced_at %s '%s' AND introduced_at %s '%s'",
            $range->getStartSign(),
            $rangeFrom->format(DateTime::ATOM),
            $range->getEndSign(),
            $rangeTo->format(DateTime::ATOM)
        );
    }

    private function typeResolver(string $value): string
    {
        return "type = '${value}'";
    }


    private function priceResolver(Range $range): string
    {
        $condition = '';
        if ($range->isStartDefined()) {
            $condition .= sprintf('price %s %s', $range->getStartSign(), $range->getStartValue());
        }

        if ($range->isEndDefined()) {
            if ($condition !== '') {
                $condition .= ' AND ';
            }
            $condition .= sprintf('price %s %s', $range->getEndSign(), $range->getEndValue());
        }

        return $condition;
    }
}
```

*Note: If you need join some tables for complex SQL you could use eg. `Doctrine\DBAL\Query\QueryBuilder` and pass it via constructor into your resolver. :)*

Then you need define your own query parser with allowed FQL tokens. Simple query parser supporting all FQL tokens could look like this:

```php
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
``` 

Finally FQL to SQL transformation process could look like this:

```php
<?php declare(strict_types = 1);

use Apicart\FQL\Tests\Integration\FilterParser;
use Apicart\FQL\Tests\Integration\Generator\SQL\Resolver\ItemFilterResolver;

$fql = 'q:"samsung" AND introducedAt:["2019-01-01 00:00:00" TO "2019-01-31 23:59:59"] AND NOT type:(tv OR "mobile phone") OR price:{10 TO *]';

$resolver = new ItemFilterResolver;
$sql = FilterParser::parse($fql, $resolver);

echo $sql; // "name ILIKE '%samsung%' AND introduced_at >= '2019-01-01T00:00:00+00:00' AND introduced_at <= '2019-01-31T23:59:59+00:00' AND (type = 'tv' OR type = 'mobile phone') OR (price > 10)"
```

For more informations about [token visitors](https://github.com/apicart/fql/tree/master/src/Generator/SQL), [fql resolvers](https://github.com/apicart/fql/tree/master/tests/Integration/Generator/SQL/Resolver) and [fql transformations](https://github.com/apicart/fql/tree/master/tests/Generator/SQL/FilterParserTest.php) see our [tests](https://github.com/apicart/fql/tree/master/tests).

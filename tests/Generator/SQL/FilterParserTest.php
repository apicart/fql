<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Generator\SQL;

use Apicart\FQL\Tests\Integration\FilterParser;
use Apicart\FQL\Tests\Integration\Generator\SQL\Resolver\ItemFilterResolver;
use PHPUnit\Framework\TestCase;

final class FilterParserTest extends TestCase
{

    public function testParse(): void
    {
        $fql = 'q:"samsung" AND introducedAt:["2019-01-01 00:00:00" TO "2019-01-31 23:59:59"]'
            . ' AND NOT type:(tv OR "mobile phone") OR (price:{"10" TO *] OR price:{"30" TO *])';
        $resolver = new ItemFilterResolver;

        $sql = FilterParser::parse($fql, $resolver);
        self::assertSame(
            "name ILIKE '%samsung%'"
            . " AND (introduced_at >= '2019-01-01T00:00:00+00:00' AND introduced_at <= '2019-01-31T23:59:59+00:00')"
            . " AND NOT ((type = 'tv' OR type = 'mobile phone')) OR ((price > 10) OR (price > 30))",
            $sql
        );
    }

}

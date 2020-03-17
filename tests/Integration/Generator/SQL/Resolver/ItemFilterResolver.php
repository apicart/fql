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
            'q' => function (string $value): string {
                return $this->queryResolver($value);
            },
            'introducedAt' => function (Range $range): string {
                return $this->introducedAtResolver($range);
            },
            'type' => function (string $value): string {
                return $this->typeResolver($value);
            },
            'price' => function (Range $range): string {
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
        $rangeFrom = new DateTime((string) $range->getStartValue());
        $rangeTo = new DateTime((string) $range->getEndValue());

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

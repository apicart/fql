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

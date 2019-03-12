<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Integration\Generator\SQL\Resolver;

use InvalidArgumentException;

abstract class AbstractFilterResolver
{

	/**
	 * @param mixed $value
	 */
	public function resolve(string $column, $value): string
	{
		$mapping = $this->getResolvers();
		if (! isset($mapping[$column])) {
			throw new InvalidArgumentException($column);
		}

		return call_user_func($mapping[$column], $value);
	}


	/**
	 * @return callable[]
	 */
	abstract protected function getResolvers(): array;

}

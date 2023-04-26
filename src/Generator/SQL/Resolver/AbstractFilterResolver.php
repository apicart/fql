<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\SQL\Resolver;

use InvalidArgumentException;

abstract class AbstractFilterResolver
{

    /**
     * @param array<int, mixed> $values
     */
    public function resolve(string $column, ...$values): string
    {
        $mapping = $this->getResolvers();
        foreach ($mapping as $pattern => $resolver) {
            $matches = [];
            if ((bool) preg_match_all("#^{$pattern}$#", $column, $matches, PREG_SET_ORDER) === false) {
                continue;
            }

            array_push($values, $matches);

            return call_user_func_array($resolver, $values);
        }

        throw new InvalidArgumentException($column);
    }


    /**
     * @return callable[]
     */
    abstract protected function getResolvers(): array;

}

<?php declare(strict_types = 1);

namespace Apicart\FQL\Value;

abstract class AbstractNode
{

    /**
     * @return AbstractNode[]
     */
    abstract public function getNodes(): array;

}

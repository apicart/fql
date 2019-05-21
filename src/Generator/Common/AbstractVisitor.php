<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Common;

use Apicart\FQL\Value\AbstractNode;

abstract class AbstractVisitor
{

    abstract public function accept(AbstractNode $node): bool;


    abstract public function visit(
        AbstractNode $node,
        ?AbstractVisitor $subVisitor = null,
        ?array $options = null
    ): string;

}

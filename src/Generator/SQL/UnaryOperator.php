<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\SQL;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\LogicalNot;
use Apicart\FQL\Value\AbstractNode;

class UnaryOperator extends AbstractVisitor
{

    public function accept(AbstractNode $node): bool
    {
        return $node instanceof LogicalNot;
    }


    public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
    {
        /** @var LogicalNot $logicalNotNode */
        $logicalNotNode = $node;

        return 'NOT (' . $subVisitor->visit($logicalNotNode->getOperand(), $subVisitor, $options) . ')';
    }

}

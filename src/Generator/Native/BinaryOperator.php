<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\LogicalAnd;
use Apicart\FQL\Token\Node\LogicalOr;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class BinaryOperator extends AbstractVisitor
{

    public function accept(AbstractNode $node): bool
    {
        return $node instanceof LogicalAnd || $node instanceof LogicalOr;
    }


    public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
    {
        if (! $node instanceof LogicalAnd && ! $node instanceof LogicalOr) {
            throw new LogicException('Implementation accepts instance of LogicalAnd or LogicalOr Node');
        }
        if ($subVisitor === null) {
            throw new LogicException('Implementation requires sub-visitor');
        }
        $clauses = [
            $subVisitor->visit($node->getLeftOperand(), $subVisitor, $options),
            $subVisitor->visit($node->getRightOperand(), $subVisitor, $options),
        ];
        return implode(" {$node->getToken()->getLexeme()} ", $clauses);
    }

}

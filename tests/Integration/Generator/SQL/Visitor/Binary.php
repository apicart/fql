<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Integration\Generator\SQL\Visitor;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\LogicalAnd;
use Apicart\FQL\Token\Node\LogicalOr;
use Apicart\FQL\Value\AbstractNode;

final class Binary extends AbstractVisitor
{

	public function accept(AbstractNode $node): bool
	{
		return $node instanceof LogicalAnd || $node instanceof LogicalOr;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		/** @var LogicalAnd|LogicalOr $logicalNode */
		$logicalNode = $node;
		$clauses = [
			$subVisitor->visit($logicalNode->getLeftOperand(), $subVisitor, $options),
			$subVisitor->visit($logicalNode->getRightOperand(), $subVisitor, $options),
		];

		return implode(" {$logicalNode->getToken()->getLexeme()} ", $clauses);
	}

}

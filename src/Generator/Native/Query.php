<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\Query as QueryNode;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class Query extends AbstractVisitor
{

	public function accept(AbstractNode $node): bool
	{
		return $node instanceof QueryNode;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		if (! $node instanceof QueryNode) {
			throw new LogicException('Implementation accepts instance of Query Node');
		}
		if ($subVisitor === null) {
			throw new LogicException('Implementation requires sub-visitor');
		}
		$clauses = [];
		foreach ($node->getNodes() as $subNode) {
			$clauses[] = $subVisitor->visit($subNode, $subVisitor, $options);
		}
		return implode(' ', $clauses);
	}

}

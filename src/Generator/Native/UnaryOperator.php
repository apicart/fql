<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\LogicalNot;
use Apicart\FQL\Token\Node\Mandatory;
use Apicart\FQL\Token\Node\Prohibited;
use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class UnaryOperator extends AbstractVisitor
{

	public function accept(AbstractNode $node): bool
	{
		return $node instanceof Mandatory || $node instanceof Prohibited || $node instanceof LogicalNot;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		if (! $node instanceof Mandatory && ! $node instanceof Prohibited && ! $node instanceof LogicalNot) {
			throw new LogicException('Implementation accepts instance of Mandatory, Prohibited or LogicalNot Node');
		}
		if ($subVisitor === null) {
			throw new LogicException('Implementation requires sub-visitor');
		}
		$clause = $subVisitor->visit($node->getOperand(), $subVisitor, $options);
		$padding = '';
		if ($node->getToken()->getType() === Tokenizer::TOKEN_LOGICAL_NOT) {
			$padding = ' ';
		}
		return "{$node->getToken()->getLexeme()}{$padding}{$clause}";
	}

}

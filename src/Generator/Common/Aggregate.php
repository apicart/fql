<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Common;

use Apicart\FQL\Value\AbstractNode;
use RuntimeException;

final class Aggregate extends AbstractVisitor
{

	/**
	 * @var AbstractVisitor[]
	 */
	private $visitors = [];


	/**
	 * Construct from the optional array of $visitors.
	 *
	 * @param AbstractVisitor[] $visitors
	 */
	public function __construct(array $visitors = [])
	{
		foreach ($visitors as $visitor) {
			$this->addVisitor($visitor);
		}
	}


	public function addVisitor(AbstractVisitor $visitor): void
	{
		$this->visitors[] = $visitor;
	}


	public function accept(AbstractNode $node): bool
	{
		return true;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		foreach ($this->visitors as $visitor) {
			if ($visitor->accept($node)) {
				return $visitor->visit($node, $this, $options);
			}
		}
		throw new RuntimeException('No visitor available for ' . get_class($node));
	}

}

<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Token\Token\Range as RangeToken;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class Range extends AbstractVisitor
{

	public function accept(AbstractNode $node): bool
	{
		return $node instanceof Term && $node->getToken() instanceof RangeToken;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		if (! $node instanceof Term) {
			throw new LogicException('Implementation accepts instance of Term Node');
		}
		$token = $node->getToken();
		if (! $token instanceof RangeToken) {
			throw new LogicException('Implementation accepts instance of Range Token');
		}
		$domainPrefix = $token->getDomain() === '' ? '' : "{$token->getDomain()}:";
		return $domainPrefix .
			$this->buildRangeStart($token) .
			' TO ' .
			$this->buildRangeEnd($token);
	}


	private function buildRangeStart(RangeToken $token): string
	{
		switch ($token->getStartType()) {
			case RangeToken::TYPE_INCLUSIVE:
				return '[' . $token->getRangeFrom();
			case RangeToken::TYPE_EXCLUSIVE:
				return '{' . $token->getRangeFrom();
			default:
				throw new LogicException(sprintf('Range start type %s is not supported', $token->getStartType()));
		}
	}


	private function buildRangeEnd(RangeToken $token): string
	{
		switch ($token->getEndType()) {
			case RangeToken::TYPE_INCLUSIVE:
				return $token->getRangeTo() . ']';
			case RangeToken::TYPE_EXCLUSIVE:
				return $token->getRangeTo() . '}';
			default:
				throw new LogicException(sprintf('Range end type %s is not supported', $token->getEndType()));
		}
	}

}

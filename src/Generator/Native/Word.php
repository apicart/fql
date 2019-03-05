<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Token\Token\Word as WordToken;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class Word extends AbstractVisitor
{

	public function accept(AbstractNode $node): bool
	{
		return $node instanceof Term && $node->getToken() instanceof WordToken;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		if (! $node instanceof Term) {
			throw new LogicException('Implementation accepts instance of Term Node');
		}
		$token = $node->getToken();
		if (! $token instanceof WordToken) {
			throw new LogicException('Implementation accepts instance of Word Token');
		}
		$domainPrefix = $token->getDomain() === '' ? '' : "{$token->getDomain()}:";
		$wordEscaped = preg_replace('/([\\\'"+\-!():#@ ])/', '\\\\$1', $token->getWord());
		return "{$domainPrefix}{$wordEscaped}";
	}

}

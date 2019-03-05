<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Token\Token\Phrase as PhraseToken;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class Phrase extends AbstractVisitor
{

	public function accept(AbstractNode $node): bool
	{
		return $node instanceof Term && $node->getToken() instanceof PhraseToken;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		if (! $node instanceof Term) {
			throw new LogicException('Implementation accepts instance of Term Node');
		}
		$token = $node->getToken();
		if (! $token instanceof PhraseToken) {
			throw new LogicException('Implementation accepts instance of Phrase Token');
		}
		$domainPrefix = $token->getDomain() === '' ? '' : "{$token->getDomain()}:";
		$phraseEscaped = preg_replace("/([\\{$token->getQuote()}])/", '\\\\$1', $token->getPhrase());
		return "{$domainPrefix}{$token->getQuote()}{$phraseEscaped}{$token->getQuote()}";
	}

}

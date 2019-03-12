<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Integration\Generator\SQL\Visitor;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Tests\Integration\Generator\SQL\Resolver\AbstractFilterResolver;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Token\Token\Word as WordToken;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class Word extends AbstractVisitor
{

	/**
	 * @var AbstractFilterResolver
	 */
	private $filterResolver;


	public function __construct(AbstractFilterResolver $filterResolver)
	{
		$this->filterResolver = $filterResolver;
	}


	public function accept(AbstractNode $node): bool
	{
		return $node instanceof Term && $node->getToken() instanceof WordToken;
	}


	public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
	{
		/** @var Term $termNode */
		$termNode = $node;
		/** @var WordToken $token */
		$token = $termNode->getToken();

		$domain = $token->getDomain();
		if ($domain === '') {
			throw new LogicException('Missing required domain');
		}

		$wordEscaped = preg_replace('/([\\\'"+\-!():#@ ])/', '\\\\$1', $token->getWord());

		return $this->filterResolver->resolve($domain, $wordEscaped);
	}

}

<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\SQL;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Generator\SQL\Resolver\AbstractFilterResolver;
use Apicart\FQL\Token\Node\Group as GroupNode;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Token\Token\Phrase as PhraseToken;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class Phrase extends AbstractVisitor
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
        return $node instanceof Term && $node->getToken() instanceof PhraseToken;
    }


    public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
    {
        /** @var Term $termNode */
        $termNode = $node;
        /** @var PhraseToken $token */
        $token = $termNode->getToken();
        $domain = $token->getDomain();

        if ($domain === '' || $domain === null) {
            $parent = $options['parent'] ?? false;
            if ($parent instanceof GroupNode) {
                $tokenLeft = $parent->getTokenLeft();
                if ($tokenLeft !== null) {
                    $domain = $tokenLeft->getDomain();
                }
            }

            if ($domain === '' || $domain === null) {
                throw new LogicException('Missing required domain');
            }
        }

        $phraseEscaped = preg_replace("/([\\{$token->getQuote()}])/", '\\\\$1', $token->getPhrase());

        return $this->filterResolver->resolve($domain, $phraseEscaped);
    }

}

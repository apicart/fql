<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\Group as GroupNode;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class Group extends AbstractVisitor
{

    public function accept(AbstractNode $node): bool
    {
        return $node instanceof GroupNode;
    }


    public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
    {
        if (! $node instanceof GroupNode) {
            throw new LogicException('Implementation accepts instance of Group Node');
        }
        if ($subVisitor === null) {
            throw new LogicException('Implementation requires sub-visitor');
        }
        $clauses = [];
        foreach ($node->getNodes() as $subNode) {
            $clauses[] = $subVisitor->visit($subNode, $subVisitor, $options);
        }
        $clauses = implode(' ', $clauses);
        $tokenLeft = $node->getTokenLeft();
        $tokenRight = $node->getTokenRight();

        $domainPrefix = $tokenLeft === null || $tokenLeft->getDomain() === '' ? '' : "{$tokenLeft->getDomain()}:";
        $delimiter = $tokenLeft === null ? '' : $tokenLeft->getDelimiter();
        $lexeme = $tokenRight === null ? '' : $tokenRight->getLexeme();

        return "{$domainPrefix}{$delimiter}{$clauses}{$lexeme}";
    }

}

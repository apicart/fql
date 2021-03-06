<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\SQL;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\Group as GroupNode;
use Apicart\FQL\Value\AbstractNode;

final class Group extends AbstractVisitor
{

    public function accept(AbstractNode $node): bool
    {
        return $node instanceof GroupNode;
    }


    public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
    {
        /** @var GroupNode $groupNode */
        $groupNode = $node;

        $clauses = [];
        foreach ($groupNode->getNodes() as $subNode) {
            $options['parent'] = $node;
            if ($subVisitor !== null) {
                $clauses[] = $subVisitor->visit($subNode, $subVisitor, $options);
            }
        }

        $clauses = implode(' ', $clauses);
        $tokenLeft = $groupNode->getTokenLeft();
        $tokenRight = $groupNode->getTokenRight();

        $delimiter = $tokenLeft === null ? '' : $tokenLeft->getDelimiter();
        $lexeme = $tokenRight === null ? '' : $tokenRight->getLexeme();

        return "{$delimiter}{$clauses}{$lexeme}";
    }

}

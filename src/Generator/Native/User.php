<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Token\Token\User as UserToken;
use Apicart\FQL\Value\AbstractNode;
use LogicException;

final class User extends AbstractVisitor
{

    public function accept(AbstractNode $node): bool
    {
        return $node instanceof Term && $node->getToken() instanceof UserToken;
    }


    public function visit(AbstractNode $node, ?AbstractVisitor $subVisitor = null, ?array $options = null): string
    {
        if (! $node instanceof Term) {
            throw new LogicException('Implementation accepts instance of Term Node');
        }
        $token = $node->getToken();
        if (! $token instanceof UserToken) {
            throw new LogicException('Implementation accepts instance of User Token');
        }
        return "{$token->getMarker()}{$token->getUser()}";
    }

}

<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Node;

use Apicart\FQL\Value\AbstractNode;
use Apicart\FQL\Value\Token;

final class Term extends AbstractNode
{

    /**
     * @var Token
     */
    private $token;


    public function __construct(Token $token)
    {
        $this->token = $token;
    }


    public function getNodes(): array
    {
        return [];
    }


    public function getToken(): Token
    {
        return $this->token;
    }

}

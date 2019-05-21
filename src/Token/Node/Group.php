<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Node;

use Apicart\FQL\Token\Token\GroupBegin;
use Apicart\FQL\Value\AbstractNode;
use Apicart\FQL\Value\Token;

final class Group extends AbstractNode
{

    /**
     * @var AbstractNode[]
     */
    private $nodes = [];

    /**
     * @var GroupBegin
     */
    private $tokenLeft;

    /**
     * @var Token
     */
    private $tokenRight;


    /**
     * @param AbstractNode[] $nodes
     */
    public function __construct(array $nodes = [], ?GroupBegin $tokenLeft = null, ?Token $tokenRight = null) {
        $this->nodes = $nodes;
        $this->tokenLeft = $tokenLeft;
        $this->tokenRight = $tokenRight;
    }


    /**
     * @return AbstractNode[]
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }


    /**
     * @param AbstractNode[] $nodes
     */
    public function setNodes(array $nodes): void
    {
        $this->nodes = $nodes;
    }


    public function getTokenLeft(): GroupBegin
    {
        return $this->tokenLeft;
    }


    public function setTokenLeft(GroupBegin $tokenLeft): void
    {
        $this->tokenLeft = $tokenLeft;
    }


    public function getTokenRight(): Token
    {
        return $this->tokenRight;
    }


    public function setTokenRight(Token $tokenRight): void
    {
        $this->tokenRight = $tokenRight;
    }

}

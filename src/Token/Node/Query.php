<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Node;

use Apicart\FQL\Value\AbstractNode;

final class Query extends AbstractNode
{

    /**
     * @var AbstractNode[]
     */
    private $nodes = [];


    /**
     * @param AbstractNode[] $nodes
     */
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }


    public function getNodes(): array
    {
        return $this->nodes;
    }

}

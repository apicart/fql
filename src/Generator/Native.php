<?php declare(strict_types = 1);

namespace Apicart\FQL\Generator;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Value\SyntaxTree;

final class Native
{

    /**
     * @var AbstractVisitor
     */
    private $visitor;


    public function __construct(AbstractVisitor $visitor)
    {
        $this->visitor = $visitor;
    }


    public function generate(SyntaxTree $syntaxTree): string
    {
        return $this->visitor->visit($syntaxTree->getRootNode());
    }

}

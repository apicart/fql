<?php declare(strict_types = 1);

namespace Apicart\FQL\Value;

class SyntaxTree
{

    /**
     * @var Correction[]
     */
    private $corrections = [];

    /**
     * @var AbstractNode
     */
    private $rootNode;

    /**
     * @var TokenSequence
     */
    private $tokenSequence;


    /**
     * @param Correction[] $corrections
     */
    public function __construct(AbstractNode $rootNode, TokenSequence $tokenSequence, array $corrections)
    {
        $this->rootNode = $rootNode;
        $this->tokenSequence = $tokenSequence;
        $this->corrections = $corrections;
    }


    public function getRootNode(): AbstractNode
    {
        return $this->rootNode;
    }


    public function getTokenSequence(): TokenSequence
    {
        return $this->tokenSequence;
    }


    /**
     * @return Correction[]
     */
    public function getCorrections(): array
    {
        return $this->corrections;
    }

}

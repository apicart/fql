<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Node;

use Apicart\FQL\Value\AbstractNode;
use Apicart\FQL\Value\Token;

final class LogicalAnd extends AbstractNode
{

    /**
     * @var AbstractNode
     */
    private $leftOperand;

    /**
     * @var AbstractNode
     */
    private $rightOperand;

    /**
     * @var Token
     */
    private $token;


    public function __construct(
        ?AbstractNode $leftOperand = null,
        ?AbstractNode $rightOperand = null,
        ?Token $token = null
    ) {
        $this->leftOperand = $leftOperand;
        $this->rightOperand = $rightOperand;
        $this->token = $token;
    }


    public function getNodes(): array
    {
        return [$this->getLeftOperand(), $this->getRightOperand()];
    }


    public function getLeftOperand(): AbstractNode
    {
        return $this->leftOperand;
    }


    public function setLeftOperand(AbstractNode $leftOperand): void
    {
        $this->leftOperand = $leftOperand;
    }


    public function getRightOperand(): AbstractNode
    {
        return $this->rightOperand;
    }


    public function setRightOperand(AbstractNode $rightOperand): void
    {
        $this->rightOperand = $rightOperand;
    }


    public function getToken(): Token
    {
        return $this->token;
    }


    public function setToken(Token $token): void
    {
        $this->token = $token;
    }

}

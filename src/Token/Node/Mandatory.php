<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Node;

use Apicart\FQL\Value\AbstractNode;
use Apicart\FQL\Value\Token;

final class Mandatory extends AbstractNode
{

	/**
	 * @var AbstractNode
	 */
	private $operand;

	/**
	 * @var Token
	 */
	private $token;


	public function __construct(?AbstractNode $operand = null, ?Token $token = null)
	{
		$this->operand = $operand;
		$this->token = $token;
	}


	public function getNodes(): array
	{
		return [$this->getOperand()];
	}


	public function getOperand(): AbstractNode
	{
		return $this->operand;
	}


	public function setOperand(AbstractNode $operand): void
	{
		$this->operand = $operand;
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

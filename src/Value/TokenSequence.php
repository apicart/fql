<?php declare(strict_types = 1);

namespace Apicart\FQL\Value;

class TokenSequence
{

	/**
	 * @var string
	 */
	private $source;

	/**
	 * @var Token[]
	 */
	private $tokens = [];


	/**
	 * @param Token[] $tokens
	 */
	public function __construct(array $tokens, string $source)
	{
		$this->tokens = $tokens;
		$this->source = $source;
	}


	/**
	 * @return Token[]
	 */
	public function getTokens(): array
	{
		return $this->tokens;
	}


	public function getSource(): string
	{
		return $this->source;
	}

}

<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;

final class Word extends Token
{

	/**
	 * @var string
	 */
	private $domain;

	/**
	 * @var string
	 */
	private $word;


	public function __construct(string $lexeme, int $position, string $domain, string $word)
	{
		$this->domain = $domain;
		$this->word = $word;

		parent::__construct(Tokenizer::TOKEN_TERM, $lexeme, $position);
	}


	public function getDomain(): string
	{
		return $this->domain;
	}


	public function getWord(): string
	{
		return $this->word;
	}

}

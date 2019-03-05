<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;

final class User extends Token
{

	/**
	 * @var string
	 */
	private $marker;

	/**
	 * @var string
	 */
	private $user;


	public function __construct(string $lexeme, int $position, string $marker, string $user)
	{
		$this->marker = $marker;
		$this->user = $user;

		parent::__construct(Tokenizer::TOKEN_TERM, $lexeme, $position);
	}


	public function getMarker(): string
	{
		return $this->marker;
	}


	public function getUser(): string
	{
		return $this->user;
	}

}

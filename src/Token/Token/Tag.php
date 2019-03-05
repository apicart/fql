<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;

final class Tag extends Token
{

	/**
	 * @var string
	 */
	private $marker;

	/**
	 * @var string
	 */
	private $tag;


	public function __construct(string $lexeme, int $position, string $marker, string $tag)
	{
		$this->marker = $marker;
		$this->tag = $tag;

		parent::__construct(Tokenizer::TOKEN_TERM, $lexeme, $position);
	}


	public function getMarker(): string
	{
		return $this->marker;
	}


	public function getTag(): string
	{
		return $this->tag;
	}

}

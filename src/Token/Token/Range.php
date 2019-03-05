<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;
use InvalidArgumentException;

final class Range extends Token
{

	public const TYPE_INCLUSIVE = 'inclusive';

	public const TYPE_EXCLUSIVE = 'exclusive';

	/**
	 * @var string
	 */
	private $domain;

	/**
	 * @var string
	 */
	private $rangeFrom;

	/**
	 * @var string
	 */
	private $rangeTo;

	/**
	 * @var string
	 */
	private $startType;

	/**
	 * @var string
	 */
	private $endType;


	public function __construct(
		string $lexeme,
		int $position,
		string $domain,
		string $rangeFrom,
		string $rangeTo,
		?string $startType,
		?string $endType
	) {
		$this->ensureValidType($startType);
		$this->ensureValidType($endType);
		parent::__construct(Tokenizer::TOKEN_TERM, $lexeme, $position);

		$this->domain = $domain;
		$this->rangeFrom = $rangeFrom;
		$this->rangeTo = $rangeTo;
		$this->startType = $startType;
		$this->endType = $endType;
	}


	public function getDomain(): string
	{
		return $this->domain;
	}


	public function getRangeFrom(): string
	{
		return $this->rangeFrom;
	}


	public function getRangeTo(): string
	{
		return $this->rangeTo;
	}


	public function getStartType(): string
	{
		return $this->startType;
	}


	public function setStartType(string $startType): void
	{
		$this->startType = $startType;
	}


	public function getEndType(): string
	{
		return $this->endType;
	}


	public function setEndType(string $endType): void
	{
		$this->endType = $endType;
	}


	private function ensureValidType(?string $type): void
	{
		if (! in_array($type, [self::TYPE_EXCLUSIVE, self::TYPE_INCLUSIVE], true)) {
			throw new InvalidArgumentException(sprintf('Invalid range type: %s', $type));
		}
	}

}

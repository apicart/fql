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
     * @var int|float|string
     */
    private $startValue;

    /**
     * @var int|float|string
     */
    private $endValue;

    /**
     * @var string|null
     */
    private $startType;

    /**
     * @var string|null
     */
    private $endType;

    /**
     * @var Flags|null
     */
    private $flags;


    /**
     * @param int|float|string $startValue
     * @param int|float|string $endValue
     */
    public function __construct(
        string $lexeme,
        int $position,
        string $domain,
        $startValue,
        $endValue,
        ?string $startType,
        ?string $endType,
        ?Flags $flags = null
    ) {
        $this->ensureValidType($startType);
        $this->ensureValidType($endType);
        parent::__construct(Tokenizer::TOKEN_TERM, $lexeme, $position);

        $this->domain = $domain;
        $this->startValue = $startValue;
        $this->endValue = $endValue;
        $this->startType = $startType;
        $this->endType = $endType;
        $this->flags = $flags;
    }


    public function getDomain(): string
    {
        return $this->domain;
    }


    /**
     * @return int|float|string
     */
    public function getStartValue()
    {
        return $this->startValue;
    }


    /**
     * @return int|float|string
     */
    public function getEndValue()
    {
        return $this->endValue;
    }


    public function getStartType(): ?string
    {
        return $this->startType;
    }


    public function setStartType(?string $startType): void
    {
        $this->startType = $startType;
    }


    public function getEndType(): ?string
    {
        return $this->endType;
    }


    public function setEndType(?string $endType): void
    {
        $this->endType = $endType;
    }


    public function getStartSign(): string
    {
        return $this->getStartType() === Range::TYPE_INCLUSIVE ? '>=' : '>';
    }


    public function getEndSign(): string
    {
        return $this->getEndType() === Range::TYPE_INCLUSIVE ? '<=' : '<';
    }


    public function isStartDefined(): bool
    {
        return $this->getStartValue() !== '*';
    }


    public function isEndDefined(): bool
    {
        return $this->getEndValue() !== '*';
    }


    public function getFlags(): ?Flags
    {
        return $this->flags;
    }


    private function ensureValidType(?string $type): void
    {
        if (! in_array($type, [self::TYPE_EXCLUSIVE, self::TYPE_INCLUSIVE], true)) {
            throw new InvalidArgumentException(sprintf('Invalid range type: %s', $type));
        }
    }

}

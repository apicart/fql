<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use DateTimeImmutable;
use Apicart\FQL\Value\Token;
use InvalidArgumentException;
use Apicart\FQL\Tokenizer\Tokenizer;

final class Range extends Token
{

    public const TYPE_INCLUSIVE = 'inclusive';

    public const TYPE_EXCLUSIVE = 'exclusive';

    public const DATE_FORMAT = 'Y-m-d';
    public const DATE_REGEX = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/';

    public const DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';
    public const DATETIME_REGEX = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])T([01]\d|2[0-3]):([0-5]\d):([0-5]\d)(\.\d{1,9})?Z$/';

    public const RELATIVE_DATE_SEPARATOR = '|';
    public const RELATIVE_DATE_REGEX = '/^(today|week|month|year)(\|(\+|-)?\d+)?$/';
    public const RELATIVE_DATE_TODAY = 'today';
    public const RELATIVE_DATE_WEEK = 'week';
    public const RELATIVE_DATE_MONTH = 'month';
    public const RELATIVE_DATE_YEAR = 'year';
    public const RELATIVE_DATE_VALUES = [
        self::RELATIVE_DATE_TODAY,
        self::RELATIVE_DATE_WEEK,
        self::RELATIVE_DATE_MONTH,
        self::RELATIVE_DATE_YEAR,
    ];

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


    public function getStartDateValue(): ?DateTimeImmutable
    {
        if ($this->isStartInDateFormat()) {
            return DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $this->getStartValue());
        }

        return null;
    }

    public function getStartDateTimeValue(): ?DateTimeImmutable
    {
        if ($this->isStartInDateTimeFormat()) {
            return DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $this->getStartValue());
        }

        return null;
    }

    public function getStartRelativeDateValue(): ?array
    {
        if ($this->isStartInRelativeDateFormat()) {
            $parts = explode(self::RELATIVE_DATE_SEPARATOR, $this->getStartValue());
            $value = $parts[0];
            $offset = isset($parts[1]) ? (int) $parts[1] : 0;

            return ['value' => $value, 'offset' => $offset];
        }

        return null;
    }


    /**
     * @return int|float|string
     */
    public function getEndValue()
    {
        return $this->endValue;
    }

    public function getEndDateValue(): ?DateTimeImmutable
    {
        if ($this->isEndInDateFormat()) {
            return DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $this->getEndValue());
        }

        return null;
    }

    public function getEndDateTimeValue(): ?DateTimeImmutable
    {
        if ($this->isEndInDateTimeFormat()) {
            return DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $this->getEndValue());
        }

        return null;
    }

    public function getEndRelativeDateValue(): ?array
    {
        if ($this->isEndInRelativeDateFormat()) {
            $parts = explode(self::RELATIVE_DATE_SEPARATOR, $this->getEndValue());
            $value = $parts[0];
            $offset = isset($parts[1]) ? (int) $parts[1] : 0;

            return ['value' => $value, 'offset' => $offset];
        }

        return null;
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

    public function isStartInDateFormat(): bool
    {
        return preg_match(self::DATE_REGEX, $this->getStartValue()) === 1;
    }

    public function isStartInDateTimeFormat(): bool
    {
        return preg_match(self::DATETIME_REGEX, $this->getStartValue()) === 1;
    }

    public function isStartInRelativeDateFormat(): bool
    {
        return preg_match(self::RELATIVE_DATE_REGEX, $this->getStartValue()) === 1;
    }

    public function isEndInDateFormat(): bool
    {
        return preg_match(self::DATE_REGEX, $this->getEndValue()) === 1;
    }

    public function isEndInDateTimeFormat(): bool
    {
        return preg_match(self::DATETIME_REGEX, $this->getEndValue()) === 1;
    }

    public function isEndInRelativeDateFormat(): bool
    {
        return preg_match(self::RELATIVE_DATE_REGEX, $this->getEndValue()) === 1;
    }


    private function ensureValidType(?string $type): void
    {
        if (! in_array($type, [self::TYPE_EXCLUSIVE, self::TYPE_INCLUSIVE], true)) {
            throw new InvalidArgumentException(sprintf('Invalid range type: %s', $type));
        }
    }

}

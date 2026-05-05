<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use DateTimeZone;
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
    public const RELATIVE_DATE_OFFSET_PERIOD_SEPARATOR = ':';
    public const RELATIVE_DATE_REGEX = '/^(today|week|month|year)(\|(\+|-)?\d+(:(day|week|month|year)s?)?)?$/';
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

    public const RELATIVE_OFFSET_PERIOD_DAY = 'day';
    public const RELATIVE_OFFSET_PERIOD_WEEK = 'week';
    public const RELATIVE_OFFSET_PERIOD_MONTH = 'month';
    public const RELATIVE_OFFSET_PERIOD_YEAR = 'year';
    public const RELATIVE_OFFSET_PERIODS = [
        self::RELATIVE_OFFSET_PERIOD_DAY,
        self::RELATIVE_OFFSET_PERIOD_WEEK,
        self::RELATIVE_OFFSET_PERIOD_MONTH,
        self::RELATIVE_OFFSET_PERIOD_YEAR,
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
            $date = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, (string) $this->getStartValue());
            return $date === false ? null : $date;
        }

        return null;
    }

    public function getStartDateTimeValue(): ?DateTimeImmutable
    {
        if ($this->isStartInDateTimeFormat()) {
            $date = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, (string) $this->getStartValue());
            return $date === false ? null : $date;
        }

        return null;
    }

    public function getStartRelativeDateValue(): ?DateTimeImmutable
    {
        if ($this->isStartInRelativeDateFormat()) {
            return self::getRelativeDate((string) $this->getStartValue());
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
            $date = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, (string) $this->getEndValue());
            return $date === false ? null : $date;
        }

        return null;
    }

    public function getEndDateTimeValue(): ?DateTimeImmutable
    {
        if ($this->isEndInDateTimeFormat()) {
            $date = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, (string) $this->getEndValue());
            return $date === false ? null : $date;
        }

        return null;
    }


    public function getEndRelativeDateValue(): ?DateTimeImmutable
    {
        if ($this->isEndInRelativeDateFormat()) {
            return self::getRelativeDate((string) $this->getEndValue());
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
        return preg_match(self::DATE_REGEX, (string) $this->getStartValue()) === 1;
    }

    public function isStartInDateTimeFormat(): bool
    {
        return preg_match(self::DATETIME_REGEX, (string) $this->getStartValue()) === 1;
    }

    public function isStartInRelativeDateFormat(): bool
    {
        return preg_match(self::RELATIVE_DATE_REGEX, (string) $this->getStartValue()) === 1;
    }

    public function isEndInDateFormat(): bool
    {
        return preg_match(self::DATE_REGEX, (string) $this->getEndValue()) === 1;
    }

    public function isEndInDateTimeFormat(): bool
    {
        return preg_match(self::DATETIME_REGEX, (string) $this->getEndValue()) === 1;
    }

    public function isEndInRelativeDateFormat(): bool
    {
        return preg_match(self::RELATIVE_DATE_REGEX, (string) $this->getEndValue()) === 1;
    }

    /**
     * @return array{base: string, offset: int, period: string|null}|null
     */
    public static function parseRelativeDateValue(string $value): ?array
    {
        if (preg_match(self::RELATIVE_DATE_REGEX, $value) !== 1) {
            return null;
        }

        $parts = explode(self::RELATIVE_DATE_SEPARATOR, $value, 2);
        $base = $parts[0];
        $offset = 0;
        $period = null;

        if (isset($parts[1])) {
            $offsetParts = explode(self::RELATIVE_DATE_OFFSET_PERIOD_SEPARATOR, $parts[1], 2);
            $offset = (int) $offsetParts[0];
            $period = isset($offsetParts[1]) ? rtrim($offsetParts[1], 's') : null;
        }

        return ['base' => $base, 'offset' => $offset, 'period' => $period];
    }


    public static function getRelativeDate(string $value): ?DateTimeImmutable
    {
        $parsed = self::parseRelativeDateValue($value);
        if ($parsed === null) {
            return null;
        }

        $base = $parsed['base'];
        $offset = $parsed['offset'];
        $period = $parsed['period'] ?? self::getDefaultPeriodForBase($base);
        $date = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        switch ($base) {
            case self::RELATIVE_DATE_TODAY:
                $anchored = $date;
                break;
            case self::RELATIVE_DATE_WEEK:
                $anchored = $date->modify('this week');
                break;
            case self::RELATIVE_DATE_MONTH:
                $anchored = $date->modify('first day of this month');
                break;
            case self::RELATIVE_DATE_YEAR:
                $anchored = $date->modify('first day of january this year');
                break;
            default:
                return null;
        }

        return $anchored->modify(($offset >= 0 ? '+' : '') . $offset . ' ' . $period . 's');
    }

    private static function getDefaultPeriodForBase(string $base): string
    {
        switch ($base) {
            case self::RELATIVE_DATE_WEEK:
                return self::RELATIVE_OFFSET_PERIOD_WEEK;
            case self::RELATIVE_DATE_MONTH:
                return self::RELATIVE_OFFSET_PERIOD_MONTH;
            case self::RELATIVE_DATE_YEAR:
                return self::RELATIVE_OFFSET_PERIOD_YEAR;
            case self::RELATIVE_DATE_TODAY:
            default:
                return self::RELATIVE_OFFSET_PERIOD_DAY;
        }
    }

    private function ensureValidType(?string $type): void
    {
        if (! in_array($type, [self::TYPE_EXCLUSIVE, self::TYPE_INCLUSIVE], true)) {
            throw new InvalidArgumentException(sprintf('Invalid range type: %s', $type));
        }
    }

}

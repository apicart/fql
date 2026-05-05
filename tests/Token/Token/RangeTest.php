<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Token\Token;

use Apicart\FQL\Token\Token\Range;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class RangeTest extends TestCase
{

    public function failingTypeDataprovider(): array
    {
        return [
            ['', 'inclusive'],
            ['', 'exclusive'],
            ['inclusive', ''],
            ['exclusive', ''],
            [null, null],
            ['other', 'inclusive'],
            ['other', 'exclusive'],
            ['inclusive', 'other'],
            ['exclusive', 'other'],
            ['inclusive', null],
            ['exclusive', null],
            [null, 'inclusive'],
            [null, 'exclusive'],
        ];
    }


    /**
     * @dataProvider failingTypeDataprovider
     */
    public function testConstructorFailsWrongType(?string $startType, ?string $endType): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Range('[a TO b]', 0, '', 'a', 'b', $startType, $endType);
    }


    /**
     * @return array<string, array{0: string, 1: array{base: string, offset: int, period: string|null}}>
     */
    public function validRelativeDateValueProvider(): array
    {
        return [
            'today without offset' => ['today', ['base' => 'today', 'offset' => 0, 'period' => null]],
            'today negative offset' => ['today|-3', ['base' => 'today', 'offset' => -3, 'period' => null]],
            'today positive offset' => ['today|+3', ['base' => 'today', 'offset' => 3, 'period' => null]],
            'today unsigned offset' => ['today|3', ['base' => 'today', 'offset' => 3, 'period' => null]],
            'month positive offset' => ['month|+2', ['base' => 'month', 'offset' => 2, 'period' => null]],
            'today with month period' => ['today|-3:month', ['base' => 'today', 'offset' => -3, 'period' => 'month']],
            'month with day period' => ['month|+2:day', ['base' => 'month', 'offset' => 2, 'period' => 'day']],
            'year with week period' => ['year|+1:week', ['base' => 'year', 'offset' => 1, 'period' => 'week']],
            'week with year period' => ['week|-2:year', ['base' => 'week', 'offset' => -2, 'period' => 'year']],
            'plural day period normalized' => ['today|-3:days', ['base' => 'today', 'offset' => -3, 'period' => 'day']],
            'plural month period normalized' => ['today|-3:months', ['base' => 'today', 'offset' => -3, 'period' => 'month']],
            'plural week period normalized' => ['year|+1:weeks', ['base' => 'year', 'offset' => 1, 'period' => 'week']],
            'plural year period normalized' => ['week|-2:years', ['base' => 'week', 'offset' => -2, 'period' => 'year']],
        ];
    }


    /**
     * @dataProvider validRelativeDateValueProvider
     * @param array{base: string, offset: int, period: string|null} $expected
     */
    public function testParseRelativeDateValueValid(string $value, array $expected): void
    {
        self::assertSame($expected, Range::parseRelativeDateValue($value));
    }


    /**
     * @return array<string, array{0: string}>
     */
    public function invalidRelativeDateValueProvider(): array
    {
        return [
            'unknown base' => ['tomorrow'],
            'empty offset' => ['today|'],
            'non-numeric offset' => ['today|abc'],
            'unsupported period' => ['today|3:hour'],
            'unsupported plural period' => ['today|3:hours'],
            'double plural' => ['today|3:dayss'],
            'missing offset value' => ['today|:month'],
            'trailing colon' => ['today|3:'],
            'period without separator' => ['today:month'],
            'empty value' => [''],
            'uppercase base' => ['TODAY'],
            'whitespace' => ['today |3'],
        ];
    }


    /**
     * @dataProvider invalidRelativeDateValueProvider
     */
    public function testParseRelativeDateValueInvalid(string $value): void
    {
        self::assertNull(Range::parseRelativeDateValue($value));
    }


    public function testGetRelativeDateTodayBackwardsCompatible(): void
    {
        $expected = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('-3 days')
            ->format('Y-m-d');

        $result = Range::getRelativeDate('today|-3');

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        assert($result instanceof DateTimeImmutable);
        self::assertSame($expected, $result->format('Y-m-d'));
    }


    public function testGetRelativeDateMonthBackwardsCompatible(): void
    {
        $expected = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('first day of this month')
            ->modify('+2 months')
            ->format('Y-m-d');

        $result = Range::getRelativeDate('month|+2');

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        assert($result instanceof DateTimeImmutable);
        self::assertSame($expected, $result->format('Y-m-d'));
    }


    public function testGetRelativeDateTodayWithMonthPeriod(): void
    {
        $expected = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('-3 months')
            ->format('Y-m-d');

        $result = Range::getRelativeDate('today|-3:month');

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        assert($result instanceof DateTimeImmutable);
        self::assertSame($expected, $result->format('Y-m-d'));
    }


    public function testGetRelativeDateMonthWithDayPeriod(): void
    {
        $expected = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('first day of this month')
            ->modify('+2 days')
            ->format('Y-m-d');

        $result = Range::getRelativeDate('month|+2:day');

        self::assertInstanceOf(DateTimeImmutable::class, $result);
        assert($result instanceof DateTimeImmutable);
        self::assertSame($expected, $result->format('Y-m-d'));
    }


    public function testGetRelativeDateInvalidReturnsNull(): void
    {
        self::assertNull(Range::getRelativeDate('today|3:hour'));
    }

}

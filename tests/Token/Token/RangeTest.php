<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Token\Token;

use Apicart\FQL\Token\Token\Range;
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

}

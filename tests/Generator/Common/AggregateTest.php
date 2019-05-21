<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Generator\Common;

use Apicart\FQL\Generator\Common\Aggregate;
use Apicart\FQL\Value\AbstractNode;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AggregateTest extends TestCase
{

    public function testAccept(): void
    {
        /** @var AbstractNode $nodeMock */
        $nodeMock = $this->getMockBuilder(AbstractNode::class)->getMock();
        self::assertTrue((new Aggregate)->accept($nodeMock));
    }


    public function testVisitThrowsException(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('No visitor available for Mock');

        /** @var AbstractNode $nodeMock */
        $nodeMock = $this->getMockBuilder(AbstractNode::class)->getMock();
        (new Aggregate)->visit($nodeMock);
    }

}

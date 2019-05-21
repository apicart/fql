<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Value;

use Apicart\FQL\Token\Node\Group;
use Apicart\FQL\Token\Node\LogicalAnd;
use Apicart\FQL\Token\Node\LogicalNot;
use Apicart\FQL\Token\Node\LogicalOr;
use Apicart\FQL\Token\Node\Mandatory;
use Apicart\FQL\Token\Node\Prohibited;
use Apicart\FQL\Token\Node\Query;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Value\AbstractNode;
use Apicart\FQL\Value\Token;
use PHPUnit\Framework\TestCase;

final class NodeTraversalTest extends TestCase
{

    public function testGroupNode(): void
    {
        /** @var AbstractNode $firstMember */
        $firstMember = $this->getMockForAbstractClass(AbstractNode::class);
        /** @var AbstractNode $secondMember */
        $secondMember = $this->getMockForAbstractClass(AbstractNode::class);
        $nodes = (new Group([$firstMember, $secondMember]))->getNodes();

        self::assertSame($firstMember, $nodes[0]);
        self::assertSame($secondMember, $nodes[1]);
    }


    public function testLogicalAndNode(): void
    {
        $leftOperand = $this->getMockForAbstractClass(AbstractNode::class);
        $rightOperand = $this->getMockForAbstractClass(AbstractNode::class);
        $nodes = (new LogicalAnd($leftOperand, $rightOperand))->getNodes();

        self::assertSame($leftOperand, $nodes[0]);
        self::assertSame($rightOperand, $nodes[1]);
    }


    public function testLogicalNotNode(): void
    {
        $operand = $this->getMockForAbstractClass(AbstractNode::class);
        $nodes = (new LogicalNot($operand))->getNodes();

        self::assertSame($operand, $nodes[0]);
    }


    public function testLogicalOrNode(): void
    {
        $leftOperand = $this->getMockForAbstractClass(AbstractNode::class);
        $rightOperand = $this->getMockForAbstractClass(AbstractNode::class);
        $nodes = (new LogicalOr($leftOperand, $rightOperand))->getNodes();

        self::assertSame($leftOperand, $nodes[0]);
        self::assertSame($rightOperand, $nodes[1]);
    }


    public function testMandatoryNode(): void
    {
        $operand = $this->getMockForAbstractClass(AbstractNode::class);
        $nodes = (new Mandatory($operand))->getNodes();

        self::assertSame($operand, $nodes[0]);
    }


    public function testProhibitedNode(): void
    {
        $operand = $this->getMockForAbstractClass(AbstractNode::class);
        $nodes = (new Prohibited($operand))->getNodes();

        self::assertSame($operand, $nodes[0]);
    }


    public function testQueryNode(): void
    {
        /** @var AbstractNode $firstMember */
        $firstMember = $this->getMockForAbstractClass(AbstractNode::class);
        /** @var AbstractNode $secondMember */
        $secondMember = $this->getMockForAbstractClass(AbstractNode::class);
        $nodes = (new Query([$firstMember, $secondMember]))->getNodes();

        self::assertSame($firstMember, $nodes[0]);
        self::assertSame($secondMember, $nodes[1]);
    }


    public function testTermNode(): void
    {
        /** @var Token $token */
        $token = $this->getMockBuilder(Token::class)->disableOriginalConstructor()->getMock();
        $nodes = (new Term($token))->getNodes();

        self::assertEmpty($nodes);
    }

}

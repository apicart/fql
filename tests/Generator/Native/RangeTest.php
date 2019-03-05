<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Generator\Native;

use Apicart\FQL\Generator\Common\AbstractVisitor;
use Apicart\FQL\Generator\Native\Range;
use Apicart\FQL\Token\Node\Mandatory;
use Apicart\FQL\Token\Node\Term;
use Apicart\FQL\Token\Token\Range as RangeToken;
use Apicart\FQL\Token\Token\Word;
use Apicart\FQL\Value\AbstractNode;
use LogicException;
use PHPUnit\Framework\TestCase;

final class RangeTest extends TestCase
{
	/**
	 * @var AbstractVisitor
	 */
	public $visitor;


	protected function setUp(): void
	{
		$this->visitor = new Range();
	}


	public function acceptDataprovider(): array
	{
		return [
			[true, new Term(new RangeToken('[a TO b]', 0, '', 'a', 'b', 'inclusive', 'inclusive'))],
			[false, new Term(new Word('word', 0, '', 'a'))],
		];
	}


	/**
	 * @dataProvider acceptDataprovider
	 */
	public function testAccepts(bool $expected, AbstractNode $node): void
	{
		self::assertSame($expected, $this->visitor->accept($node));
	}


	public function visitDataprovider(): array
	{
		return [
			['[a TO b]', new Term(new RangeToken('[a TO b]', 0, '', 'a', 'b', 'inclusive', 'inclusive'))],
			['[a TO b}', new Term(new RangeToken('[a TO b}', 0, '', 'a', 'b', 'inclusive', 'exclusive'))],
			['{a TO b}', new Term(new RangeToken('{a TO b}', 0, '', 'a', 'b', 'exclusive', 'exclusive'))],
			['{a TO b]', new Term(new RangeToken('{a TO b]', 0, '', 'a', 'b', 'exclusive', 'inclusive'))],
		];
	}


	/**
	 * @dataProvider visitDataprovider
	 */
	public function testVisit(string $expected, AbstractNode $node): void
	{
		self::assertSame($expected, $this->visitor->visit($node));
	}


	public function visitWrongNodeDataprovider(): array
	{
		return [[new Mandatory], [new Term(new Word('word', 0, '', 'a'))]];
	}


	/**
	 * @dataProvider visitWrongNodeDataprovider
	 */
	public function testVisitWrongNodeFails(AbstractNode $node): void
	{
		$this->expectException(LogicException::class);
		$this->visitor->visit($node);
	}


	public function testVisitUnknownRangeStartTypeFails(): void
	{
		$token = new RangeToken('{a TO b}', 0, '', 'a', 'b', 'inclusive', 'inclusive');
		$token->setStartType('unknown');
		$node = new Term($token);
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Range start type unknown is not supported');
		$this->visitor->visit($node);
	}


	public function testVisitUnknownRangeEndTypeFails(): void
	{
		$token = new RangeToken('{a TO b}', 0, '', 'a', 'b', 'inclusive', 'inclusive');
		$token->setEndType('unknown');
		$node = new Term($token);
		$this->expectException(LogicException::class);
		$this->expectExceptionMessage('Range end type unknown is not supported');
		$this->visitor->visit($node);
	}

}

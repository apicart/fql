<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Tokenizer;

use Apicart\FQL\Tokenizer\AbstractTokenExtractor;
use Apicart\FQL\Tokenizer\Full;
use Apicart\FQL\Tokenizer\Text;
use Apicart\FQL\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use ReflectionClass;
use RuntimeException;

final class TokenizerTest extends TestCase
{

	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage PCRE regex error code: 2
	 */
	public function testExtractThrowsExceptionPCRE(): void
	{
		/** @var PHPUnit_Framework_MockObject_MockObject $extractor */
		$extractor = $this->getMockBuilder(AbstractTokenExtractor::class)
			->setMethods(['getExpressionTypeMap'])
			->getMockForAbstractClass();

		$extractor->expects(self::once())
			->method('getExpressionTypeMap')
			->willReturn([
				'/(?:\D+|<\d+>)*[!?]/' => Tokenizer::TOKEN_WHITESPACE,
			]);

		$extractor->extract('foobar foobar foobar', 0);
	}


	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Could not extract term token from the given data
	 */
	public function testFullExtractTermTokenThrowsException(): void
	{
		$extractor = new Full();
		$reflectedClass = new ReflectionClass($extractor);
		$reflectedProperty = $reflectedClass->getProperty('expressionTypeMap');
		$reflectedProperty->setAccessible(true);
		$reflectedProperty->setValue([
			'/(?<lexeme>foobar)/' => Tokenizer::TOKEN_TERM,
		]);
		$extractor->extract('foobar', 0);
	}


	/**
	 * @expectedException RuntimeException
	 * @expectedExceptionMessage Could not extract term token from the given data
	 */
	public function testTextExtractTermTokenThrowsException(): void
	{
		$extractor = new Text();
		$reflectedClass = new ReflectionClass($extractor);
		$reflectedProperty = $reflectedClass->getProperty('expressionTypeMap');
		$reflectedProperty->setAccessible(true);
		$reflectedProperty->setValue([
			'/(?<lexeme>foobar)/' => Tokenizer::TOKEN_TERM,
		]);
		$extractor->extract('foobar', 0);
	}

}

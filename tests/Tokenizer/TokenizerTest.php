<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Tokenizer;

use Apicart\FQL\Tokenizer\AbstractTokenExtractor;
use Apicart\FQL\Tokenizer\Full;
use Apicart\FQL\Tokenizer\Text;
use Apicart\FQL\Tokenizer\Tokenizer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;

final class TokenizerTest extends TestCase
{

    public function testExtractThrowsExceptionPCRE(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('PCRE regex error code: 2');

        $extractorMock = $this->getMockBuilder(AbstractTokenExtractor::class)
            ->setMethods(['getExpressionTypeMap'])
            ->getMockForAbstractClass();

        $extractorMock->expects(self::once())
            ->method('getExpressionTypeMap')
            ->willReturn([
                '/(?:\D+|<\d+>)*[!?]/' => Tokenizer::TOKEN_WHITESPACE,
            ]);

        /** @var AbstractTokenExtractor $extractor */
        $extractor = $extractorMock;
        $extractor->extract('foobar foobar foobar', 0);
    }


    public function testFullExtractTermTokenThrowsException(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Could not extract term token from the given data');

        $extractor = new Full();
        $reflectedClass = new ReflectionClass($extractor);
        $reflectedProperty = $reflectedClass->getProperty('expressionTypeMap');
        $reflectedProperty->setAccessible(true);
        $reflectedProperty->setValue([
            '/(?<lexeme>foobar)/' => Tokenizer::TOKEN_TERM,
        ]);
        $extractor->extract('foobar', 0);
    }


    public function testTextExtractTermTokenThrowsException(): void
    {
        self::expectException(RuntimeException::class);
        self::expectExceptionMessage('Could not extract term token from the given data');

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

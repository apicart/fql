<?php declare(strict_types = 1);

namespace Apicart\FQL\Tests\Tokenizer;

use Apicart\FQL\Token\Token\GroupBegin as GroupBeginToken;
use Apicart\FQL\Token\Token\Phrase as PhraseToken;
use Apicart\FQL\Token\Token\Word as WordToken;
use Apicart\FQL\Tokenizer\AbstractTokenExtractor;
use Apicart\FQL\Tokenizer\Text;
use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;

final class TextTest extends FullTest
{
    /**
     * @var array
     */
    protected static $fixtureOverride = [];


    public static function setUpBeforeClass(): void
    {
        self::$fixtureOverride = [
            '#tag' => [new WordToken('#tag', 0, '', '#tag')],
            '\#tag' => [new WordToken('\#tag', 0, '', '\#tag')],
            '#_tag-tag' => [new WordToken('#_tag-tag', 0, '', '#_tag-tag')],
            '#tag+' => [new WordToken('#tag+', 0, '', '#tag+')],
            '#tag-' => [new WordToken('#tag-', 0, '', '#tag-')],
            '#tag!' => [new WordToken('#tag!', 0, '', '#tag!')],
            "#tag\n" => [new WordToken('#tag', 0, '', '#tag'), new Token(Tokenizer::TOKEN_WHITESPACE, "\n", 4)],
            '#tag ' => [new WordToken('#tag', 0, '', '#tag'), new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 4)],
            '#tag(' => [new WordToken('#tag', 0, '', '#tag'), new GroupBeginToken('(', 4, '(', null)],
            '#tag)' => [new WordToken('#tag', 0, '', '#tag'), new Token(Tokenizer::TOKEN_GROUP_END, ')', 4)],
            '@user' => [new WordToken('@user', 0, '', '@user')],
            '@user.user' => [new WordToken('@user.user', 0, '', '@user.user')],
            '\@user' => [new WordToken('\@user', 0, '', '\@user')],
            '@_user-user' => [new WordToken('@_user-user', 0, '', '@_user-user')],
            '@user+' => [new WordToken('@user+', 0, '', '@user+')],
            '@user-' => [new WordToken('@user-', 0, '', '@user-')],
            '@user!' => [new WordToken('@user!', 0, '', '@user!')],
            "@user\n" => [new WordToken('@user', 0, '', '@user'), new Token(Tokenizer::TOKEN_WHITESPACE, "\n", 5)],
            '@user ' => [new WordToken('@user', 0, '', '@user'), new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 5)],
            '@user(' => [new WordToken('@user', 0, '', '@user'), new GroupBeginToken('(', 5, '(', null)],
            '@user)' => [new WordToken('@user', 0, '', '@user'), new Token(Tokenizer::TOKEN_GROUP_END, ')', 5)],
            '[a  TO b]' => [
                new WordToken('[a', 0, '', '[a'),
                new Token(Tokenizer::TOKEN_WHITESPACE, '  ', 2),
                new WordToken('TO', 4, '', 'TO'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 6),
                new WordToken('b]', 7, '', 'b]'),
            ],
            '[a TO b}' => [
                new WordToken('[a', 0, '', '[a'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 2),
                new WordToken('TO', 3, '', 'TO'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 5),
                new WordToken('b}', 6, '', 'b}'),
            ],
            '{a TO b}' => [
                new WordToken('{a', 0, '', '{a'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 2),
                new WordToken('TO', 3, '', 'TO'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 5),
                new WordToken('b}', 6, '', 'b}'),
            ],
            '{a TO b]' => [
                new WordToken('{a', 0, '', '{a'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 2),
                new WordToken('TO', 3, '', 'TO'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 5),
                new WordToken('b]', 6, '', 'b]'),
            ],
            '[2017-01-01 TO 2017-01-05]' => [
                new WordToken('[2017-01-01', 0, '', '[2017-01-01'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 11),
                new WordToken('TO', 12, '', 'TO'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 14),
                new WordToken('2017-01-05]', 15, '', '2017-01-05]'),
            ],
            '[20    TO   *]' => [
                new WordToken('[20', 0, '', '[20'),
                new Token(Tokenizer::TOKEN_WHITESPACE, '    ', 3),
                new WordToken('TO', 7, '', 'TO'),
                new Token(Tokenizer::TOKEN_WHITESPACE, '   ', 9),
                new WordToken('*]', 12, '', '*]'),
            ],
            '[* TO 20]' => [
                new WordToken('[*', 0, '', '[*'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 2),
                new WordToken('TO', 3, '', 'TO'),
                new Token(Tokenizer::TOKEN_WHITESPACE, ' ', 5),
                new WordToken('20]', 6, '', '20]'),
            ],
            'domain:domain:' => [new WordToken('domain:domain:', 0, '', 'domain:domain:')],
            'some.domain:some.domain:'
            => [new WordToken('some.domain:some.domain:', 0, '', 'some.domain:some.domain:')],
            'domain:domain:domain:domain' => [
                new WordToken('domain:domain:domain:domain', 0, '', 'domain:domain:domain:domain'),
            ],
            'domain\:' => [new WordToken('domain\:', 0, '', 'domain\:')],
            'domain\::' => [new WordToken('domain\::', 0, '', 'domain\::')],
            'domain:word' => [new WordToken('domain:word', 0, '', 'domain:word')],
            'domain\:word' => [new WordToken('domain\:word', 0, '', 'domain\:word')],
            'domain:"phrase"' => [
                new WordToken('domain:', 0, '', 'domain:'),
                new PhraseToken('"phrase"', 7, '', '"', 'phrase'),
            ],
            'some.domain:"phrase"' => [
                new WordToken('some.domain:', 0, '', 'some.domain:'),
                new PhraseToken('"phrase"', 12, '', '"', 'phrase'),
            ],
            'domain\:"phrase"' => [
                new WordToken('domain\:', 0, '', 'domain\:'),
                new PhraseToken('"phrase"', 8, '', '"', 'phrase'),
            ],
            'domain:(one)' => [
                new WordToken('domain:', 0, '', 'domain:'),
                new GroupBeginToken('(', 7, '(', ''),
                new WordToken('one', 8, '', 'one'),
                new Token(Tokenizer::TOKEN_GROUP_END, ')', 11),
            ],
            'some.domain:(one)' => [
                new WordToken('some.domain:', 0, '', 'some.domain:'),
                new GroupBeginToken('(', 12, '(', ''),
                new WordToken('one', 13, '', 'one'),
                new Token(Tokenizer::TOKEN_GROUP_END, ')', 16),
            ],
        ];
    }


    /**
     * @dataProvider providerForTestTokenize
     *
     * @param string $string
     */
    public function testTokenize($string, array $expectedTokens): void
    {
        $expectedTokens = $this->getExpectedFixtureWithOverride($string, $expectedTokens);
        parent::testTokenize($string, $expectedTokens);
    }


    /**
     * @param string $string
     *
     * @return Token[]
     */
    protected function getExpectedFixtureWithOverride($string, array $expectedTokens)
    {
        if (isset(self::$fixtureOverride[$string])) {
            return self::$fixtureOverride[$string];
        }
        return $expectedTokens;
    }


    protected function getTokenExtractor(): AbstractTokenExtractor
    {
        return new Text;
    }

}

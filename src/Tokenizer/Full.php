<?php declare(strict_types = 1);

namespace Apicart\FQL\Tokenizer;

use Apicart\FQL\Token\Token\Phrase;
use Apicart\FQL\Token\Token\Range;
use Apicart\FQL\Token\Token\Tag;
use Apicart\FQL\Token\Token\User;
use Apicart\FQL\Token\Token\Word;
use Apicart\FQL\Value\Token;
use RuntimeException;

final class Full extends AbstractTokenExtractor
{

    /**
     * Map of regex expressions to Token types.
     *
     * @var array
     */
    private static $expressionTypeMap = [
        '/(?<lexeme>[\s]+)/Au' => Tokenizer::TOKEN_WHITESPACE,
        '/(?<lexeme>\+)/Au' => Tokenizer::TOKEN_MANDATORY,
        '/(?<lexeme>-)/Au' => Tokenizer::TOKEN_PROHIBITED,
        '/(?<lexeme>!)/Au' => Tokenizer::TOKEN_LOGICAL_NOT_2,
        '/(?<lexeme>\))/Au' => Tokenizer::TOKEN_GROUP_END,
        '/(?<lexeme>NOT)(?:[\s"()+\-!]|$)/Au' => Tokenizer::TOKEN_LOGICAL_NOT,
        '/(?<lexeme>(?:AND|&&))(?:[\s"()+\-!]|$)/Au' => Tokenizer::TOKEN_LOGICAL_AND,
        '/(?<lexeme>(?:OR|\|\|))(?:[\s"()+\-!]|$)/Au' => Tokenizer::TOKEN_LOGICAL_OR,
        '/(?<lexeme>(?:(?<domain>[a-zA-Z_][a-zA-Z0-9_\-.\[\]\*]*):)?(?<delimiter>\())/Au' => Tokenizer::TOKEN_GROUP_BEGIN,
        '/(?<lexeme>(?:(?<marker>(?<!\\\\)\#)(?<tag>[a-zA-Z0-9_][a-zA-Z0-9_\-.]*)))(?:[\s"()+!]|$)/Au'
        => Tokenizer::TOKEN_TERM,
        '/(?<lexeme>(?:(?<marker>(?<!\\\\)@)(?<user>[a-zA-Z0-9_][a-zA-Z0-9_\-.]*)))(?:[\s"()+!]|$)/Au'
        => Tokenizer::TOKEN_TERM,
        '/(?<lexeme>(?:(?<domain>[a-zA-Z_][a-zA-Z0-9_\-.\[\]\*]*):)?(?<quote>(?<!\\\\)["])(?<phrase>.*?)(?:(?<!\\\\)' .
        '(?P=quote)))/Aus' => Tokenizer::TOKEN_TERM,
        '/(?<lexeme>(?:(?<domain>[a-zA-Z_][a-zA-Z0-9_\-.\[\]\*]*):)?(?<rangeStartSymbol>[\[\{])' .
        '(?<rangeFrom>([a-zA-Z0-9\,\._-]+|\*)|(?<quoteFrom>(?<!\\\\)["]).*(?:(?<!\\\\)(?P=quoteFrom)))[\s]+TO[\s]+' .
        '(?<rangeTo>([a-zA-Z0-9\,\._-]+|\*)|(?<quoteTo>(?<!\\\\)["]).*(?:(?<!\\\\)(?P=quoteTo)))' .
        '(?<rangeEndSymbol>[\]\}]))/Aus' => Tokenizer::TOKEN_TERM,
        '/(?<lexeme>(?:(?<domain>[a-zA-Z_][a-zA-Z0-9_\-.\[\]\*]*):)?(?<word>(?:\\\\\\\\|\\\\ |\\\\\(|\\\\\)|\\\\"|' .
        '[^"()\s])+?))(?:(?<!\\\\)["]|\(|\)|$|\s)/Au' => Tokenizer::TOKEN_TERM,
    ];


    protected function getExpressionTypeMap(): array
    {
        return self::$expressionTypeMap;
    }


    protected function createTermToken(int $position, array $data): Token
    {
        $lexeme = $data['lexeme'];
        switch (true) {
            case isset($data['rangeStartSymbol']) && isset($data['rangeEndSymbol']):
                $startValue = str_replace(',', '.', str_replace('"', '', $data['rangeFrom']));
                $endValue = str_replace(',', '.', str_replace('"', '', $data['rangeTo']));

                return new Range(
                    $lexeme,
                    $position,
                    $data['domain'],
                    is_array($startValue) ? reset($startValue) : $startValue,
                    is_array($endValue) ? reset($endValue) : $endValue,
                    $this->getRangeTypeBySymbol($data['rangeStartSymbol']),
                    $this->getRangeTypeBySymbol($data['rangeEndSymbol'])
                );

            case isset($data['word']):
                return new Word(
                    $lexeme,
                    $position,
                    $data['domain'],
                    // un-backslash special characters
                    preg_replace('/(?:\\\\(\\\\|(["+\-!():#@ ])))/', '$1', $data['word'])
                );

            case isset($data['phrase']):
                $quote = $data['quote'];
                return new Phrase(
                    $lexeme,
                    $position,
                    $data['domain'],
                    $quote,
                    // un-backslash quote
                    preg_replace('/(?:\\\\([' . $quote . ']))/', '$1', $data['phrase'])
                );

            case isset($data['tag']):
                return new Tag($lexeme, $position, $data['marker'], $data['tag']);

            case isset($data['user']):
                return new User($lexeme, $position, $data['marker'], $data['user']);
        }
        throw new RuntimeException('Could not extract term token from the given data');
    }


    protected function getRangeTypeBySymbol(string $symbol): string
    {
        if (in_array($symbol, ['{', '}'], true)) {
            return Range::TYPE_EXCLUSIVE;
        }
        return Range::TYPE_INCLUSIVE;
    }

}

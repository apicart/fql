<?php declare(strict_types = 1);

namespace Apicart\FQL\Tokenizer;

use Apicart\FQL\Token\Token\GroupBegin;
use Apicart\FQL\Token\Token\Phrase;
use Apicart\FQL\Token\Token\Word;
use Apicart\FQL\Value\Token;
use RuntimeException;

final class Text extends AbstractTokenExtractor
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
        '/(?<lexeme>\()/Au' => Tokenizer::TOKEN_GROUP_BEGIN,
        '/(?<lexeme>(?<quote>(?<!\\\\)["])(?<phrase>.*?)(?:(?<!\\\\)(?P=quote)))/Aus' => Tokenizer::TOKEN_TERM,
        '/(?<lexeme>(?<word>(?:\\\\\\\\|\\\\ |\\\\\(|\\\\\)|\\\\"|[^"()\s])+?))(?:(?<!\\\\)["]|\(|\)|$|\s)/Au'
        => Tokenizer::TOKEN_TERM,
    ];


    protected function getExpressionTypeMap(): array
    {
        return self::$expressionTypeMap;
    }


    protected function createTermToken(int $position, array $data): Token
    {
        $lexeme = $data['lexeme'];
        switch (true) {
            case isset($data['word']):
                return new Word(
                    $lexeme,
                    $position,
                    '',
                    // un-backslash special chars
                    preg_replace('/(?:\\\\(\\\\|(["+\-!() ])))/', '$1', $data['word'])
                );
            case isset($data['phrase']):
                $quote = $data['quote'];
                return new Phrase(
                    $lexeme,
                    $position,
                    '',
                    $quote,
                    // un-backslash quote
                    preg_replace('/(?:\\\\([' . $quote . ']))/', '$1', $data['phrase'])
                );
        }
        throw new RuntimeException('Could not extract term token from the given data');
    }


    protected function createGroupBeginToken(int $position, array $data): GroupBegin
    {
        return new GroupBegin($data['lexeme'], $position, $data['lexeme'], '');
    }

}

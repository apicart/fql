<?php declare(strict_types = 1);

namespace Apicart\FQL\Tokenizer;

use Apicart\FQL\Contract\Tokenizer\TokenizerInterface;
use Apicart\FQL\Token\Token\Range;
use Apicart\FQL\Value\TokenSequence;

final class Tokenizer implements TokenizerInterface
{

    /**
     * Represents the whitespace in the input string.
     */
    public const TOKEN_WHITESPACE = 1;

    /**
     * Combines two adjoining elements with logical AND.
     */
    public const TOKEN_LOGICAL_AND = 2;

    /**
     * Combines two adjoining elements with logical OR.
     */
    public const TOKEN_LOGICAL_OR = 4;

    /**
     * Applies logical NOT to the next (right-side) element.
     */
    public const TOKEN_LOGICAL_NOT = 8;

    /**
     * Applies logical NOT to the next (right-side) element.
     *
     * This is an alternative to the TOKEN_LOGICAL_NOT, with the difference that
     * parser will expect it's placed next (left) to the element it applies to,
     * without the whitespace in between.
     */
    public const TOKEN_LOGICAL_NOT_2 = 16;

    /**
     * Mandatory operator applies to the next (right-side) element and means
     * that the element must be present. There must be no whitespace between it
     * and the element it applies to.
     */
    public const TOKEN_MANDATORY = 32;

    /**
     * Prohibited operator applies to the next (right-side) element and means
     * that the element must not be present. There must be no whitespace between
     * it and the element it applies to.
     */
    public const TOKEN_PROHIBITED = 64;

    /**
     * Left side delimiter of a group.
     *
     * Group is used to group elements in order to form a sub-query.
     *
     * @see GroupBegin
     */
    public const TOKEN_GROUP_BEGIN = 128;

    /**
     * Right side delimiter of a group.
     *
     * Group is used to group elements in order to form a sub-query.
     */
    public const TOKEN_GROUP_END = 256;

    /**
     * Term token type represents a category of term type tokens.
     *
     * This type is intended to be used as an extension point through subtyping.
     *
     * @see Phrase
     * @see Tag
     * @see User
     * @see Word
     * @see Range
     */
    public const TOKEN_TERM = 512;

    /**
     * Bailout token.
     *
     * If token could not be recognized, next character is extracted into a
     * token of this type. Ignored by parser.
     */
    public const TOKEN_BAILOUT = 1024;

    /**
     * @var AbstractTokenExtractor
     */
    private $tokenExtractor;


    public function __construct(AbstractTokenExtractor $tokenExtractor)
    {
        $this->tokenExtractor = $tokenExtractor;
    }


    public function tokenize(string $string): TokenSequence
    {
        $length = mb_strlen($string);
        $position = 0;
        $tokens = [];
        while ($position < $length) {
            $token = $this->tokenExtractor->extract($string, $position);
            $position += mb_strlen($token->getLexeme());
            $tokens[] = $token;
        }
        return new TokenSequence($tokens, $string);
    }

}

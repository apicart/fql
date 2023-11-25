<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;

final class Phrase extends Token
{

    /**
     * @var string|null
     */
    private $domain;

    /**
     * @var string
     */
    private $quote;

    /**
     * @var string
     */
    private $phrase;

    /**
     * @var Flags|null
     */
    private $flags;


    public function __construct(
        string $lexeme,
        int $position,
        string $domain,
        string $quote,
        string $phrase,
        ?Flags $flags = null
    )
    {
        $this->domain = $domain;
        $this->quote = $quote;
        $this->phrase = $phrase;
        $this->flags = $flags;

        parent::__construct(Tokenizer::TOKEN_TERM, $lexeme, $position);
    }


    public function getDomain(): ?string
    {
        return $this->domain;
    }


    public function getQuote(): string
    {
        return $this->quote;
    }


    public function getPhrase(): string
    {
        return $this->phrase;
    }


    public function getFlags(): ?Flags
    {
        return $this->flags;
    }

}

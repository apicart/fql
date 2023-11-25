<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;

final class Word extends Token
{

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $word;

    /**
     * @var Flags|null
     */
    private $flags;


    public function __construct(string $lexeme, int $position, string $domain, string $word, ?Flags $flags = null)
    {
        $this->domain = $domain;
        $this->word = $word;
        $this->flags = $flags;

        parent::__construct(Tokenizer::TOKEN_TERM, $lexeme, $position);
    }


    public function getDomain(): string
    {
        return $this->domain;
    }


    public function getWord(): string
    {
        return $this->word;
    }


    public function getFlags(): ?Flags
    {
        return $this->flags;
    }

}

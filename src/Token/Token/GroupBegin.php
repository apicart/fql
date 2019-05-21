<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

use Apicart\FQL\Tokenizer\Tokenizer;
use Apicart\FQL\Value\Token;

final class GroupBegin extends Token
{

    /**
     * Holds group's left side delimiter string.
     *
     * @var string
     */
    private $delimiter;

    /**
     * @var string|null
     */
    private $domain;


    public function __construct(string $lexeme, int $position, string $delimiter, ?string $domain)
    {
        $this->delimiter = $delimiter;
        $this->domain = $domain;

        parent::__construct(Tokenizer::TOKEN_GROUP_BEGIN, $lexeme, $position);
    }


    public function getDelimiter(): string
    {
        return $this->delimiter;
    }


    public function getDomain(): ?string
    {
        return $this->domain;
    }

}

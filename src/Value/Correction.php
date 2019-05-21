<?php declare(strict_types = 1);

namespace Apicart\FQL\Value;

class Correction
{

    /**
     * @var mixed
     */
    private $type;

    /**
     * @var Token[]
     */
    private $tokens = [];


    /**
     * @param mixed $type
     * @param Token[] ...$tokens
     */
    public function __construct($type, Token ...$tokens)
    {
        $this->type = $type;
        $this->tokens = $tokens;
    }


    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }


    public function getTokens(): array
    {
        return $this->tokens;
    }

}

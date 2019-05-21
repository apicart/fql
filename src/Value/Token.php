<?php declare(strict_types = 1);

namespace Apicart\FQL\Value;

class Token
{

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $lexeme;

    /**
     * @var int
     */
    private $position;


    public function __construct(int $type, string $lexeme, int $position)
    {
        $this->type = $type;
        $this->lexeme = $lexeme;
        $this->position = $position;
    }


    public function getType(): int
    {
        return $this->type;
    }


    public function getLexeme(): string
    {
        return $this->lexeme;
    }


    public function getPosition(): int
    {
        return $this->position;
    }

}

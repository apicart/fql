<?php declare(strict_types = 1);

namespace Apicart\FQL\Token\Token;

final class Flags
{

    /**
     * @var string
     */
    private $marker;

    /**
     * @var string
     */
    private $flags;


    public function __construct(string $marker, string $flags)
    {
        $this->marker = $marker;
        $this->flags = $flags;
    }


    public function getMarker(): string
    {
        return $this->marker;
    }


    public function getFlags(): string
    {
        return $this->flags;
    }

}

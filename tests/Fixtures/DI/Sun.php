<?php

namespace SergiX44\Hydrator\Tests\Fixtures\DI;

class Sun
{
    private string $from;

    public function __construct(string $from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }
}

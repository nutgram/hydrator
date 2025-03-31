<?php

namespace SergiX44\Hydrator\Tests\Fixtures\DI;

class Leaves
{
    public int $n;

    private ?Sun $sun = null;

    public function __construct(?Sun $sun = null)
    {
        $this->sun = $sun;
    }

    /**
     * @return Sun|null
     */
    public function getSun(): ?Sun
    {
        return $this->sun;
    }
}

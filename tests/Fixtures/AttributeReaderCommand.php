<?php

namespace DMT\Test\CommandBus\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;

class AttributeReaderCommand
{
    #[Assert\NotNull()]
    protected ?string $prop = null;

    public function getProp(): ?string
    {
        return $this->prop;
    }

    public function setProp($prop): void
    {
        $this->prop = $prop;
    }
}

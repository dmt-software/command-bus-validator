<?php

namespace DMT\Test\CommandBus\Fixtures;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class ClassMetadataCommand
{
    /**
     * @var string
     */
    protected $prop;

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('prop', new Assert\NotNull());
    }

    /**
     * @return string
     */
    public function getProp(): ?string
    {
        return $this->prop;
    }

    /**
     * @param string $prop
     */
    public function setProp(string $prop): void
    {
        $this->prop = $prop;
    }
}

<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait EnableTrait
{
    #[ORM\Column(options: ['default' => false])]
    private ?bool $enable = null;

    public function isEnable(): ?bool
    {
        return $this->enable;
    }

    public function setEnable(bool $enable): static
    {
        $this->enable = $enable;

        return $this;
    }
}

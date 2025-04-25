<?php

namespace App\Entity\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

trait CreatorTrait
{
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $creator = null;

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;
        return $this;
    }
}

<?php

namespace App\Entity;

use App\Repository\ActivityListRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ActivityListRepository::class)]
#[ORM\Table(name: 'tbl_activity_list')]
class ActivityList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contained = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_verified = null;

    #[ORM\OneToOne(mappedBy: 'activitylist', cascade: ['persist', 'remove'])]
    private ?Internship $internship = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContained(): ?string
    {
        return $this->contained;
    }

    public function setContained(?string $contained): static
    {
        $this->contained = $contained;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->is_verified;
    }

    public function setIsVerified(?bool $is_verified): static
    {
        $this->is_verified = $is_verified;

        return $this;
    }

    public function getInternship(): ?Internship
    {
        return $this->internship;
    }

    public function setInternship(?Internship $internship): static
    {
        // unset the owning side of the relation if necessary
        if ($internship === null && $this->internship !== null) {
            $this->internship->setActivitylist(null);
        }

        // set the owning side of the relation if necessary
        if ($internship !== null && $internship->getActivitylist() !== $this) {
            $internship->setActivitylist($this);
        }

        $this->internship = $internship;

        return $this;
    }
}

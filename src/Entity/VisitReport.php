<?php

namespace App\Entity;

use App\Repository\VisitReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisitReportRepository::class)]
class VisitReport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contained = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_verified = null;

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
}

<?php

namespace App\Entity;

use App\Repository\VisitReportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreatorTrait;

#[ORM\Entity(repositoryClass: VisitReportRepository::class)]
#[ORM\Table(name: 'tbl_visit_report')]
class VisitReport
{
    use CreatorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $contained = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_verified = null;

    #[ORM\OneToOne(mappedBy: 'visitreport', cascade: ['persist', 'remove'])]
    private ?Internship $internship = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

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
            $this->internship->setVisitreport(null);
        }

        // set the owning side of the relation if necessary
        if ($internship !== null && $internship->getVisitreport() !== $this) {
            $internship->setVisitreport($this);
        }

        $this->internship = $internship;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }
}

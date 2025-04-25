<?php

namespace App\Entity;

use App\Repository\InternshipRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreatorTrait;

#[ORM\Entity(repositoryClass: InternshipRepository::class)]
#[ORM\Table(name: 'tbl_internship')]
class Internship
{
    use CreatorTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $start_date = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $end_date = null;

    #[ORM\Column(nullable: true)]
    private ?bool $is_verified = null;

    #[ORM\ManyToOne(inversedBy: 'internships')]
    private ?User $intern = null;

    #[ORM\ManyToOne(inversedBy: 'internships')]
    private ?School $school = null;

    #[ORM\ManyToOne(inversedBy: 'internships')]
    private ?Company $company = null;


    #[ORM\OneToOne(inversedBy: 'internship', cascade: ['persist', 'remove'])]
    private ?VisitReport $visitreport = null;

    #[ORM\OneToOne(inversedBy: 'internship', cascade: ['persist', 'remove'])]
    private ?ActivityList $activitylist = null;

    #[ORM\ManyToOne(inversedBy: 'internships')]
    private ?Speciality $Speciality = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->start_date;
    }

    public function setStartDate(?\DateTimeInterface $start_date): static
    {
        $this->start_date = $start_date;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->end_date;
    }

    public function setEndDate(?\DateTimeInterface $end_date): static
    {
        $this->end_date = $end_date;

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

    public function getIntern(): ?User
    {
        return $this->intern;
    }

    public function setIntern(?User $intern): static
    {
        $this->intern = $intern;

        return $this;
    }

    public function getSchool(): ?School
    {
        return $this->school;
    }

    public function setSchool(?School $school): static
    {
        $this->school = $school;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getVisitreport(): ?VisitReport
    {
        return $this->visitreport;
    }

    public function setVisitreport(?VisitReport $visitreport): static
    {
        $this->visitreport = $visitreport;

        return $this;
    }

    public function getActivitylist(): ?ActivityList
    {
        return $this->activitylist;
    }

    public function setActivitylist(?ActivityList $activitylist): static
    {
        $this->activitylist = $activitylist;

        return $this;
    }

    public function getSpeciality(): ?Speciality
    {
        return $this->Speciality;
    }

    public function setSpeciality(?Speciality $Speciality): static
    {
        $this->Speciality = $Speciality;

        return $this;
    }

}

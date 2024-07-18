<?php

namespace App\Entity;

use App\Repository\PartnerCompanyRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Company;
use App\Entity\Partner;


#[ORM\Entity(repositoryClass: PartnerCompanyRepository::class)]
#[ORM\Table(name: "PartnerCompany")]
class PartnerCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\ID]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    #[ORM\ID]
    #[ORM\JoinColumn(nullable: false)]
    private ?Partner $Partner = null;

    #[ORM\Column]
    private ?float $percent = null;



    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }
    

    public function getPartner(): ?Partner
    {
        return $this->Partner;
    }

    public function setPartner(?Partner $Partner): static
    {
        $this->Partner = $Partner;

        return $this;
    }

    public function getPercent(): ?float
    {
        return $this->percent;
    }

    public function setPercent(float $percent): static
    {
        $this->percent = $percent;

        return $this;
    }
    public function getId(): ?float
    {
        return $this->id;
    }

}

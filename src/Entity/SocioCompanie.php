<?php

namespace App\Entity;

use App\Repository\SocioCompaineRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Company;
use App\Entity\Socio;


#[ORM\Entity(repositoryClass: SocioCompaineRepository::class)]
class SocioCompany
{

    #[ORM\ManyToOne]
    #[ORM\ID]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\ManyToOne]
    #[ORM\ID]
    #[ORM\JoinColumn(nullable: false)]
    private ?Socio $Socio = null;

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

    public function getSocio(): ?Socio
    {
        return $this->Socio;
    }

    public function setSocio(?Socio $Socio): static
    {
        $this->Socio = $Socio;

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
}

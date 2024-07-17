<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PartnerCompanie;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: "companies")]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomeFantasia = null;

    #[ORM\Column(length: 14)]
    private ?string $cnpj = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    
    #[ORM\OneToMany(targetEntity:"PartnerCompanie", mappedBy:"companie")]
    private $Partners;

    public function __construct()
    {
        $this->Partners = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomeFantasia(): ?string
    {
        return $this->nomeFantasia;
    }

    public function setNomeFantasia(string $nomeFantasia): static
    {
        $this->nomeFantasia = $nomeFantasia;

        return $this;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj): static
    {
        $this->cnpj = $cnpj;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getPartners()
    {
        return $this->Partners;
    }

    public function addPartner(Partner $Partner, float $percent): void
    {
        $PartnerCompany = new PartnerCompany();
        $PartnerCompany->setCompany($this);
        $PartnerCompany->setPartner($Partner);
        $PartnerCompany->setPercent($percent);
    
        $this->Partners->add($PartnerCompany);
    }

    public function removerPartner(Partner $Partner): void
    {
        foreach ($this->Partners as $PartnerCompany) {
            if ($PartnerCompany->getPartner() == $Partner) {
                $this->Partners->removeElement($PartnerCompany);
                return;
            }
        }
    }

    public function verificarPartner(Partner $Partner): bool
    {
        foreach ($this->Partners as $PartnerCompany) {
            if ($PartnerCompany->getPartner()->getId() === $Partner->getId()) {
                return true;
            }else{
                return false;
            }
        }
    }


}

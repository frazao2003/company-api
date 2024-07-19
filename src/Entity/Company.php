<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PartnerCompany;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ORM\Table(name: "company")]
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

    
    #[ORM\OneToMany(targetEntity:"PartnerCompany", mappedBy:"company", cascade:["persist"])]
    private $Partners;

    #[ORM\Column]
    private ?float $percent = null;

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
            }
            
        }
        return false;
    }
    public function getPartnerCompany(Partner $partner): ?PartnerCompany
    {
        foreach ($this->Partners as $partnerCompany) {
            if ($partnerCompany->getPartner()->getId() === $partner->getId()) {
                return $partnerCompany;
            }
        }
        return null;
    }

    public function formatarResponseCompany()
    {
        $Partnerscompany = $this->getPartners();

           $companyData = [
               'id'=>$this->getId(),
               'nomeFantasia'=> $this->getNomeFantasia(),
               'cnpj' => $this->getCnpj(),
               'percent' => $this->getPercent()
           ];
   
   
           $PartnersData = [];
           foreach ($Partnerscompany as $Partnercompany) {
               $PartnersData[] = [
                   'partner' => [
                       'nome' =>$Partnercompany->getPartner()->getNome(),
                       'cpf' => $Partnercompany->getPartner()->getCpf(),
                   ],
                   'percent' => $Partnercompany->getPercent()
               ];
           }
           $data = [
               "company" => $companyData,
               'partners' => $PartnersData
           ];

           return $data;

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

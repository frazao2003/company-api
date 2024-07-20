<?php

namespace App\Entity;

use App\Repository\PartnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\PartnerCompany;
use App\Repository\PartnerCompanyRepository;

#[ORM\Entity(repositoryClass: PartnerRepository::class)]
#[ORM\Table(name: "partner")]
class Partner
{
    private PartnerCompanyRepository $partnerCompanyRepository;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Nome = null;

    #[ORM\Column(length: 11)]
    private ?string $Cpf = null;

    /**
     * @var Collection<int, Companie>
     */
    #[ORM\OneToMany(targetEntity:"PartnerCompany", mappedBy:"company", cascade:['persist'])]
    private $company;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    
    public function __construct()
    {
        $this->company = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->Nome;
    }

    public function setNome(string $Nome): static
    {
        $this->Nome = $Nome;

        return $this;
    }

    public function getCpf(): ?string
    {
        return $this->Cpf;
    }

    public function setCpf(string $Cpf): static
    {
        $this->Cpf = $Cpf;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getCompany()
    {
        return $this->company;
    }
    public function addCompany(Company $company, float $percent): void
    {
        $PartnerCompany = new PartnerCompany();
        $PartnerCompany->setCompany($company);
        $PartnerCompany->setPartner($this);
        $PartnerCompany->setPercent($percent);
    
        $this->company->add($PartnerCompany);
    }
    public function removerCompany(Company $company): void
    {
        foreach ($this->company as $PartnerCompany) {
            if ($PartnerCompany->getPartner() == $company) {
                $this->company->removeElement($PartnerCompany);
                return;
            }
        }
    }

}


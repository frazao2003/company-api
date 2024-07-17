<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\SocioCompanie;

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

    
    #[ORM\OneToMany(targetEntity:"SocioCompanie", mappedBy:"companie")]
    private $socios;

    public function __construct()
    {
        $this->socios = new ArrayCollection();
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

    public function getSocios()
    {
        return $this->socios;
    }

    public function addSocio(Socio $socio, float $percent): void
    {
        $socioCompany = new SocioCompany();
        $socioCompany->setCompany($this);
        $socioCompany->setSocio($socio);
        $socioCompany->setPercent($percent);
    
        $this->socios->add($socioCompany);
    }

    public function removerSocio(Socio $socio): void
    {
        foreach ($this->socios as $socioCompany) {
            if ($socioCompany->getSocio() == $socio) {
                $this->socios->removeElement($socioCompany);
                return;
            }
        }
    }

    public function verificarSocio(Socio $socio): bool
    {
        foreach ($this->socios as $socioCompany) {
            if ($socioCompany->getSocio()->getId() === $socio->getId()) {
                return true;
            }else{
                return false;
            }
        }
    }


}

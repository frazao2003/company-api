<?php
namespace  App\Dto;

class PartnerFilter{

    public ?string $name;
    public ?string $cpf;

    public function __construct(PartnerFilter $partnerFilter = null)
    {
        $this->name = $partnerFilter ? $partnerFilter->name : null;
        $this->cpf = $partnerFilter ? $partnerFilter->cpf : null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCpf(): ?string
    {
        return $this->cpf;
    }

    public function setCpf(?string $cpf): self
    {
        $this->cpf = $cpf;
        return $this;
    }
}
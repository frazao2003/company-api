<?php

namespace App\Dto;

class PartnerDTO{

    public string $name;

    public string $cpf;

     public function __construct(string $name, string $cpf) {
        $this->name = $name;
        $this->cpf = $cpf;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCpf(): string
    {
        return $this->cpf;
    }

    public function setCpf(string $cpf): self
    {
        $this->cpf = $cpf;
        return $this;
    }
}
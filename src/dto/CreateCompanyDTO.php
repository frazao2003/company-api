<?php

namespace App\Dto;

class CreateCompanyDTO{
    public string $name;
    public string $cnpj;

    public function __construct(string $name, string $cnpj)
    {
        $this->name = $name;
        $this->cnpj = $cnpj;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCnpj(): string
    {
        return $this->cnpj;
    }

    public function setCnpj(string $cnpj): void
    {
        $this->cnpj = $cnpj;
    }
}
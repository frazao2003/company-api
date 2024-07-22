<?php

namespace App\Dto;

class CompanyFilter
{
    public ?string $name;
    public ?string $cnpj;

    public function __construct(?string $name = null, ?string $cnpj = null)
    {
        $this->name = $name;
        $this->cnpj = $cnpj;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCnpj(): ?string
    {
        return $this->cnpj;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function setCnpj(?string $cnpj): void
    {
        $this->cnpj = $cnpj;
    }
}
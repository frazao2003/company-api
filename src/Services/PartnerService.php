<?php

namespace App\Service;

use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use App\Utils\Validator;
use App\Repository\PartnerCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Partner;

class PartnerService{
    
    private CompanyRepository $companyRepository;
    private PartnerRepository $partnerRepository;
    private EntityManagerInterface $entityManager;
    private PartnerCompanyRepository $partnerCompanyRepository;

    public function __construct(
        CompanyRepository $companyRepository, 
        PartnerRepository $partnerRepository, 
        EntityManagerInterface $entityManager,
        PartnerCompanyRepository $partnerCompanyRepository
    )
    {
        $this->companyRepository = $companyRepository;
        $this->partnerRepository = $partnerRepository;
        $this->entityManager = $entityManager;
        $this->partnerCompanyRepository = $partnerCompanyRepository;
    }

    public function getAll(){
        //Chama todos os partner do banco de dados
        $partners = $this->partnerRepository->findAll();
        $data = [];
        //formata os dados do response
        foreach($partners as $partner){
            $partnerData  = [
                'nome' =>$partner->getNome(),
                'cpf' =>$partner->getCpf()
            ];
            $partnerCompanies = $this->partnerCompanyRepository->findAllByPartner($partner);
            $companyData = [];
            foreach($partnerCompanies as $partnerCompany){
                $companyData = [
                    'nomeFantasia'=> $partnerCompany->getCompany()->getNomeFantasia(),
                    'cnpj' => $partnerCompany->getCompany()->getCnpj(),
                    'percent' => $partnerCompany->getPercent()
                ];
            }
            $data [] = [
                'partner'=> $partnerData,
                'company' => $companyData
            ];
            return $data;
        }
    }

    public function create($cpf, $nome){
        //valida os campos do request
        if(!Validator::validarCPF($cpf)) throw new \Exception('CPF inválido');

        if(!Validator::isOnlyLettersAndSpaces($nome)) throw new \Exception('This field can only have letters');
        //inicia um novo partner 
        $partner = new Partner();
        $partner->setNome($nome);
        $partner->setCpf($cpf);
        $partner->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $partner->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        //persiste os dados
        $this->partnerRepository->add($partner, true);
        return $partner;
    }

    public function getByCpf($cpf){
        //valida i cof
        if(!Validator::validarCPF($cpf)) throw new \Exception('CPF inválido');
        //busca no banco e valida a existência
        $partner = $this->partnerRepository->findOneByCpf($cpf);
        if(!$partner) throw new \Exception('partner was not found');
        //formata o response
        $data = $partner->formataCompanyResponse();
    }

    public function update($id, $nome, $cpf){
        //busca o partner pelo id e valida a existência
        $partner = $this->partnerRepository->find($id);
        if(!$partner) throw new \Exception('partner was not found');
        if($nome){
            $partner->setNome($nome);
        }
        //valida o cpf
        if(!Validator::validarCPF($cpf)) throw new \Exception('CPF inválido');
        if($cpf){
            $partner->setCpf($cpf);
        }
        $this->entityManager->flush();
        return $partner;
    }

    public function delete($cpf){
        //busca no banco de dados e valida existência
        $partner = $this->partnerRepository->findOneByCpf($cpf);
        if(!$partner) throw new \Exception('partner was not found');
        //busca todos os partnerCompany associados a esse ártner
        $partnerCompanies = $this->partnerCompanyRepository->findAllByPartner($partner);
        //deleta todos e atualiza o percent da company associada
        foreach($partnerCompanies as $partnerCompany){
            $company = $partnerCompany->getCompany();
            $company->setPercent($company->getPercent() + $partnerCompany->getPercent());
            $this->entityManager->remove($partnerCompany);
            $this->entityManager->persist($company);
            $this->entityManager->flush();

        }
        //remove o partner
        $this->partnerRepository->remove($partner, true);
        return $partner;
    }

}
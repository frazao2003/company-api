<?php

namespace App\Service;

use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use App\Utils\Validator;
use App\Repository\PartnerCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Partner;
use App\Utils\MascaraCPFeCNPJ;

class PartnerService{
    
    private PartnerRepository $partnerRepository;
    private EntityManagerInterface $entityManager;
    private PartnerCompanyRepository $partnerCompanyRepository;
    private FormateResponseDTO $formateResponseDTO;
    private MascaraCPFeCNPJ $mascaraCpfeCnpj;



    public function __construct(
        PartnerRepository $partnerRepository, 
        EntityManagerInterface $entityManager,
        PartnerCompanyRepository $partnerCompanyRepository,
        FormateResponseDTO $formateResponseDTO,
        MascaraCPFeCNPJ $mascaraCPFeCNPJ

    )
    {
        $this->partnerRepository = $partnerRepository;
        $this->entityManager = $entityManager;
        $this->partnerCompanyRepository = $partnerCompanyRepository;
        $this->formateResponseDTO = $formateResponseDTO;
        $this->mascaraCpfeCnpj = $mascaraCPFeCNPJ;
    }

    public function getAll(){
        //Chama todos os partner do banco de dados
        $partners = $this->partnerRepository->findAll();
        $data = [];
        //formata os dados do response
        foreach($partners as $partner){
            $cpfMascarado = $this->mascaraCpfeCnpj->mascaraCPF($partner->getCpf());
            $partnerData  = [
                'nome' =>$partner->getNome(),
                'cpf' =>$cpfMascarado
            ];
            $partnerCompanies = $this->partnerCompanyRepository->findAllByPartner($partner);
            $companyData = [];
            foreach($partnerCompanies as $partnerCompany){
                $cnpjMascarado = $this->mascaraCpfeCnpj->mascaraCNPJ($partnerCompany->getCompany()->getCnpj());
                $companyData = [
                    'nomeFantasia'=> $partnerCompany->getCompany()->getNomeFantasia(),
                    'cnpj' => $cnpjMascarado,
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
        $data = $this->formateResponseDTO->formatarPartnerResponse($partner);
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
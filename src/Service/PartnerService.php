<?php

namespace App\Service;

use App\Dto\PartnerDTO;
use App\Dto\PartnerFilter;
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
    /**
     * Retorna uma lista de todos os parceiros e suas respectivas empresas.
     * 
     * @return array
     */
    public function filterPartner(PartnerFilter $partnerFilter):array{
        //Chama todos os partner do banco de dados
        $partners = $this->partnerRepository->findByFilter($partnerFilter);
        $data = [];
        foreach($partners as $partner) 
        {
            $cpfMascarado = $this->mascaraCpfeCnpj->mascaraCPF($partner->getCpf());
            $data[] = [
                "id"=> $partner->getId(),
                "name"=> $partner->getName(),
                "cpf"=> $cpfMascarado,
            ];
        }
       
        return $data;
        
    }
    /**
     * Cria um novo parceiro.
     * 
     * @param string $cpf
     * @param string $nome
     * @return Partner
     * @throws Exception
     */
    public function create(PartnerDTO $partnerDTO):Partner{
        //valida os campos do request
        if(!Validator::validarCPF($partnerDTO->getCpf())) throw new \Exception('CPF inválido');

        if(!Validator::isOnlyLettersAndSpaces($partnerDTO->getName())) throw new \Exception('This field can only have letters');
        //inicia um novo partner 
        $partner = new Partner();
        $partner->setName($partnerDTO->getName());
        $partner->setCpf($partnerDTO->getCpf());
        $partner->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));

        //persiste os dados
        $this->partnerRepository->add($partner, true);
        return $partner;
    }
    /**
     * Busca um parceiro pelo id.
     * 
     * @param string $cpf
     * @return array
     * @throws Exception
     */
    public function getById($id):array{
        $partner = $this->partnerRepository->find($id);
        if(!$partner) throw new \Exception('partner was not found');
        //formata o response
        $data = $this->formateResponseDTO->formatePartnerResponse($partner);
        return $data;
    }
    /**
     * Atualiza um parceiro.
     * 
     * @param int $id
     * @param string|null $nome
     * @param string|null $cpf
     * @return Partner
     * @throws Exception
     */
    public function update($id, $nome, $cpf):Partner{
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
    /**
     * Deleta um parceiro pelo CPF.
     * 
     * @param string $cpf
     * @return Partner
     * @throws Exception
     */
    public function delete($id):Partner{
        //busca no banco de dados e valida existência
        $partner = $this->partnerRepository->find($id);
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
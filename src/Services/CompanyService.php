<?php

namespace App\Service;

use App\Entity\Company;

use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use App\Utils\Validator;
use App\Repository\PartnerCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;



class CompanyService{


    
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
        $companies = $this->companyRepository->findAll();
        $companiesData = [];
        $data = [];
        //Formatar o array response
        foreach($companies as $company){
            $companiesData []= [
                'nomeFantasia' => $company->getNomeFantasia(),
                'cnpj' => $company->getCnpj(),
                'percent' => $company->getPercent()
            ];
            $partnercompany = $company->getPartners();
            $partnerData = [];
            foreach($partnercompany as $partnercompany){
                $partnerData [] = [
                    'nome' => $partnercompany->getPartner()->getNome(),
                    'cpf' => $partnercompany->getPartner()->getCpf(),
                    'percent' => $partnercompany->getPercent()
                ];
            }
            $data[] = [
                'company' => $companiesData,
                'partners' => $partnerData
            ];
            $companiesData = [];
        }
        return $data;
    }
    public function create($cnpj, $nomeFantasia){
         //validar o conteúdo do array
         if (!$cnpj) {
            throw new \Exception('CNPJ is missing');
        }
        
        if (!$nomeFantasia) {
            throw new \Exception('Nome Fantasia is missing');
        }

        //validar o cnpj
        if(!Validator::validarCNPJ($cnpj)) throw new \Exception('CNPJ inválido');
        
        // validar se o cnpj já não está cadastrado
        if ($this->companyRepository->existsByCnpj($cnpj)) {
            throw new \Exception('The company CNPJ is already registered ');
        } 
        //criar nova entidade Company
        $company = new Company();
        $company->setNomeFantasia($nomeFantasia);
        $company->setCnpj($cnpj);
        $company->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $company->setPercent(100);
        //persistir os dados no banco de dados
        $this->companyRepository->add($company, true);
        return $company;
    }
      
    public function update($id, $cnpj, $nomeFantasia){
        // buscar a company pelo cnpj
        $company = $this->companyRepository->find($id);
        //validar se a company existe no banco de dados
        if(!$company) throw new \Exception('company was not found');        
        //validar o conteúdo do array

        //validar o cnpj
        if(!Validator::validarCNPJ($cnpj)) throw new \Exception('CNPJ inválido');
        
        // validar se o cnpj já não está cadastrado
        if ($this->companyRepository->existsByCnpj($cnpj)) {
            throw new \Exception('The company CNPJ is already registered ');
        }
        //atualizar dados
        if ($cnpj) {
            $company->setCnpj($cnpj);
        }
        
        if ($nomeFantasia) {
            $company->setNomeFantasia($nomeFantasia);
        } 
        // persistir dados
        $this->entityManager->flush();
        $data = $company->formatarResponseCompany();
        return $data;
    }

    public function delete($cnpj){
         //buscar a company pelo id
         $company = $this->companyRepository->findOneByCnpj($cnpj);
         //validar se a company existe no banco de dados
         if(!$company) throw new \Exception('company was not found');
         //deletar todos os partner associados 
         foreach ($company->getPartners() as $Partnercompany) {
             $this->entityManager->remove($Partnercompany);
         }
         //persistir dados no banco de dados
         $this->companyRepository->remove($company, true);
         //formatar para response
         $data = $company->formatarResponseCompany();
         return $data;
    }
    public function getByNomeFantasia($nomeFantasia){
         //buscar company pelo nomeFantasisa
         $company = $this->companyRepository->findOneByNomeFantasia($nomeFantasia);
         //validar existência
         if(!$company) throw new \Exception('company was not found');
         
         //formatar para response
         $data = $company->formatarResponseCompany();
         return $data;
    
    }

    public function getByCnpj($cnpj){
        //validar CNPJ
        if(!Validator::validarCNPJ($cnpj)) throw new \Exception('CNPJ inválido');
        //buscar a company pelo CNPJ
        $company = $this->companyRepository->findOneByCnpj($cnpj);
        //validar a existência
        if(!$company) throw new \Exception('company was not found');
        //formatar para response
        $data = $company->formatarResponseCompany();
        return $data;
    }

    public function addPartner(String $cpf, String $cnpj, Float $percent){

        //validar CPF e CNPJ
        if(!Validator::validarCNPJ($cnpj)) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($cpf)) throw new \Exception('CPF inválido');
        
        //buscar company pelo CNPJ
        $company = $this->companyRepository->findOneByCnpj($cnpj);
        if(!$company) throw new \Exception('company was not found');
        //buscar partner pelo CPF
        $Partner = $this->partnerRepository->findOneByCpf($cpf);
        if(!$Partner) throw new \Exception('Partner was not found');
        // validar se o partner já está associado com a company
        if ($company->verificarPartner($Partner)) {
            throw new \Exception('Partner is not associated with this company.');
        }
        //validar se a porcentagem está disponível na company
        if($company->getPercent() < $percent){
            throw new \Exception('The percentage exceeds the available percentage of the company.');
        }


        //persistir dados
        $company->addPartner($Partner,$percent);
        $company->setPercent($company->getPercent() -$percent);
        $this->entityManager->flush();
        //formatar response
        $data = $company->formatarResponseCompany();
        return $data;  
    }
    public function removePartner($cpf, $cnpj){
        //validar CPF e CNPJ
        if(!Validator::validarCNPJ($cnpj)) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($cpf)) throw new \Exception('CPF inválido');
        
        //buscar company pelo CNPJ
        $company = $this->companyRepository->findOneByCnpj($cnpj);
        if(!$company) throw new \Exception('company was not found');
        
        //buscar partner pelo CPF
        $Partner = $this->partnerRepository->findOneByCpf($cpf);
        if(!$Partner) throw new \Exception('Partner was not found');
        //validar se o partner está relacionado a company
        $partnerCompany = $this->partnerCompanyRepository->findOneBy(['company' => $company, 'Partner' => $Partner]);
        if (!$partnerCompany) {
            throw new \Exception('Partner is not associated with this company.');
        }
         
        //persistir dados
        $this->entityManager->remove($partnerCompany);
        $company->setPercent($company->getPercent() + $partnerCompany->getPercent());
        $this->entityManager->flush();
        //formatar response
        $data = $company->formatarResponseCompany();
        return $data;
    }

    public function updatePercent($cnpj, $cpf, $percent){
        //valida cpf e cnpj
        if(!Validator::validarCNPJ($cnpj)) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($cpf)) throw new \Exception('CPF inválido');
        
        //busca e valida se a company existe
        $company = $this->companyRepository->findOneByCnpj($cnpj);
        if(!$company) throw new \Exception('company was not found');
        //busca e valida se a company existe
        $Partner = $this->partnerRepository->findOneByCpf($cpf);
        if(!$Partner) throw new \Exception('Partner was not found');
        // valida a porcentagem disponível na company
        if($company->getPercent() < $percent){
            throw new \Exception('The percentage exceeds the available percentage of the company.');
        }
        //valida se o partner está associado a company
        $partnercompany = $company->getPartnerCompany($Partner);
        if (!$partnercompany) {
            throw new \Exception('Partner isnt associated with this company.');
        }
        //calcula novo percentual da company pós att
        if($partnercompany->getPercent() < $percent){
            $percentCompany =$company->getPercent() - ($percent - $partnercompany->getPercent());
            $company->setPercent($percentCompany);
        }if($partnercompany->getPercent() > $percent){
            $percentCompany = $company->getPercent() + ($partnercompany->getPercent() - $percent);
            $company->setPercent($percentCompany);
        }
        $partnercompany->setPercent($percent);
        //persiste os dados
        $this->entityManager->persist($company);
        $this->entityManager->flush();
        //formata o response
        $data = $company->formatarResponseCompany();

        return $data;
    }
}

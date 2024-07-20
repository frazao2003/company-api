<?php

namespace App\Service;

use App\Entity\Company;

use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use App\Utils\Validator;
use App\Repository\PartnerCompanyRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Utils\MascaraCPFeCNPJ;



class CompanyService{


    
    private CompanyRepository $companyRepository;
    private PartnerRepository $partnerRepository;
    private EntityManagerInterface $entityManager;
    private PartnerCompanyRepository $partnerCompanyRepository;
    private FormateResponseDTO $formateResponseDTO;
    private MascaraCPFeCNPJ $mascaraCpfeCnpj;



    public function __construct(
        CompanyRepository $companyRepository, 
        PartnerRepository $partnerRepository, 
        EntityManagerInterface $entityManager,
        PartnerCompanyRepository $partnerCompanyRepository,
        FormateResponseDTO $formateResponseDTO,
        MascaraCPFeCNPJ $mascaraCPFeCNPJ
    )
    {
        $this->companyRepository = $companyRepository;
        $this->partnerRepository = $partnerRepository;
        $this->entityManager = $entityManager;
        $this->partnerCompanyRepository = $partnerCompanyRepository;
        $this->formateResponseDTO = $formateResponseDTO;
        $this->mascaraCpfeCnpj = $mascaraCPFeCNPJ;

    }
    /**
     * Retorna uma lista de todas as empresas e seus respectivos parceiros.
     * 
     * @return array
     */
    public function getAll():array{
        $companies = $this->companyRepository->findAll();
        $companiesData = [];
        $data = [];
        //Formatar o array response
        foreach($companies as $company){
            $cnpjMascarado = $this->mascaraCpfeCnpj->mascaraCNPJ($company->getCnpj());
            $companiesData []= [
                'nomeFantasia' => $company->getNomeFantasia(),
                'cnpj' => $cnpjMascarado,
                'percent' => $company->getPercent()
            ];
            $partnercompanies = $this->partnerCompanyRepository->findAllByCompany($company);
            $partnerData = [];
            foreach($partnercompanies as $partnercompany){
                $cpfMascarado = $this->mascaraCpfeCnpj->mascaraCPF($partnercompany->getPartner()->getCpf());
                $partnerData [] = [
                    'nome' => $partnercompany->getPartner()->getNome(),
                    'cpf' => $cpfMascarado,
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

    /**
     * Cria uma nova empresa.
     * 
     * @param string $cnpj
     * @param string $nomeFantasia
     * @return Company
     * @throws Exception
     */    
    public function create($cnpj, $nomeFantasia):Company{
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
    /**
     * Atualiza uma empresa existente.
     * 
     * @param int $id
     * @param string|null $cnpj
     * @param string|null $nomeFantasia
     * @return array
     * @throws Exception
     */  
    public function update($id, $cnpj, $nomeFantasia):array{
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
        $data = $this->formateResponseDTO->formatarResponseCompany($company);
        return $data;
    }
    /**
     * Deleta uma empresa pelo seu CNPJ.
     * 
     * @param string $cnpj
     * @return array
     * @throws Exception
     */
    public function delete($cnpj):array{
         //buscar a company pelo id
         $company = $this->companyRepository->findOneByCnpj($cnpj);
         //validar se a company existe no banco de dados
         if(!$company) throw new \Exception('company was not found');
         //deletar todos os partner associados 
         foreach ($this->partnerCompanyRepository->findAllByCompany($company) as $Partnercompany) {
             $this->entityManager->remove($Partnercompany);
         }
         //persistir dados no banco de dados
         $this->companyRepository->remove($company, true);
         //formatar para response
         $data = $this->formateResponseDTO->formatarResponseCompany($company);
         return $data;
    }
    /**
     * Busca uma empresa pelo nome fantasia.
     * 
     * @param string $nomeFantasia
     * @return array
     * @throws Exception
     */
    public function getByNomeFantasia($nomeFantasia):array{
         //buscar company pelo nomeFantasisa
         $company = $this->companyRepository->findOneByNomeFantasia($nomeFantasia);
         //validar existência
         if(!$company) throw new \Exception('company was not found');
         
         //formatar para response
         $data = $this->formateResponseDTO->formatarResponseCompany($company);
         return $data;
    
    }
    /**
     * Busca uma empresa pelo CNPJ.
     * 
     * @param string $cnpj
     * @return array
     * @throws Exception
     */
    public function getByCnpj($cnpj):array{
        //validar CNPJ
        if(!Validator::validarCNPJ($cnpj)) throw new \Exception('CNPJ inválido');
        //buscar a company pelo CNPJ
        $company = $this->companyRepository->findOneByCnpj($cnpj);
        //validar a existência
        if(!$company) throw new \Exception('company was not found');
        //formatar para response
        $data = $this->formateResponseDTO->formatarResponseCompany($company);
        return $data;
    }
    /**
     * Adiciona um parceiro a uma empresa.
     * 
     * @param string $cpf
     * @param string $cnpj
     * @param float $percent
     * @return array
     * @throws Exception
     */
    public function addPartner(String $cpf, String $cnpj, Float $percent):array{

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
        $data = $this->formateResponseDTO->formatarResponseCompany($company);
        return $data;  
    }
    /**
     * Remove um parceiro de uma empresa.
     * 
     * @param string $cpf
     * @param string $cnpj
     * @return array
     * @throws Exception
     */
    public function removePartner($cpf, $cnpj):array{
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
        $data = $this->formateResponseDTO->formatarResponseCompany($company);
        return $data;
    }
    /**
     * Atualiza a porcentagem de um parceiro em uma empresa.
     * 
     * @param string $cnpj
     * @param string $cpf
     * @param float $percent
     * @return array
     * @throws Exception
     */
    public function updatePercent($cnpj, $cpf, $percent):array{
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
        $data = $this->formateResponseDTO->formatarResponseCompany($company);

        return $data;
    }
}

<?php

namespace App\Controller;

use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Validator;
use App\Entity\PartnerCompany;
use App\Repository\PartnerCompanyRepository;
use Symfony\Component\Serializer\SerializerInterface;

class CompanyController extends AbstractController
{
    #[Route('/companys', name: 'app_company', methods: ['GET'])]
    public function getAll(CompanyRepository $companyRepository, SerializerInterface $serializer): JsonResponse
    {
        $companies = $companyRepository->findAll();
        $companiesData = [];
        foreach($companies as $company){
            $companiesData []= [
                'nomeFantasia' => $company->getNomeFantasia(),
                'cnpj' => $company->getCnpj(),
            ];
            $partnercompany = $company->getPartners();
            $partnerData = [];
            foreach($partnercompany as $partnercompany){
                $partnerData [] = [
                    'nome' => $partnercompany->getPartner()->getNome(),
                    'cpf' => $partnercompany->getPartner()->getCpf()
                ];
            }
            $data[] = [
                'company' => $companiesData,
                'partners' => $partnerData
            ];
            $companiesData = [];
        }
        return $this->json(['data' => $data], 200);
        
    }
    #[Route('/companys', name: 'company_create', methods: ['POST'])]
    public function create(Request $request, CompanyRepository $companyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
         // Verificar se a chave 'cnpj' está presente no array $data
        if (!array_key_exists('cnpj', $data)) {
            throw new \Exception('CNPJ is missing');
        }

        // Verificar se a chave 'nomeFantasia' está presente no array $data
        if (!array_key_exists('nomeFantasia', $data)) {
            throw new \Exception('Nome Fantasia is missing');
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        $company = new Company();
        $company->setNomeFantasia($data['nomeFantasia']);
        $company->setCnpj($data['cnpj']);
        $company->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $companyRepository->add($company, true);
        return $this->json([
            'message' => 'company Created Successfully',
            'data' => $company
        ], 201);
    }

    #[Route('/companys', name: 'company_single', methods: ['GET'])]
    public function getSingle(int $company, CompanyRepository $companyRepository): JsonResponse
    {
        $company = $companyRepository->find($company);
        if(!$company) throw new \Exception('company was not found');
        
        return $this->json([
            'data' => $company,
        ],200);
    }
    //Atualizar uma empresa
    #[Route('/companys/{company}', name: 'company_update', methods: ['PUT'])]
    public function update(int $company, Request $request, ManagerRegistry $d, CompanyRepository $companyRepository): JsonResponse
    {
        
        $company = $companyRepository->find($company);
        if(!$company) throw new \Exception('company was not found');
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        $company->setNomeFantasia($data['nomeFantasia']);
        $company->setCnpj($data['cnpj']);
        $d->getManager() ->flush();
        return $this->json([
            'message' => 'company Updated Successfully',
        ], 201);
    }
    //Deletar uma empresa
    #[Route('/companys/{company}', name: 'company_delete', methods: ['DELETE'])]
    public function delete(int $company, CompanyRepository $companyRepository, ManagerRegistry $doctrine): JsonResponse
    {
        $company = $companyRepository->find($company);
        if(!$company) throw new \Exception('company was not found');

        foreach ($company->getPartners() as $Partnercompany) {
            $doctrine->getManager()->remove($Partnercompany);
        }

        $companyRepository->remove($company, true);


        return $this->json([
            'message' => 'company deleted Successfully',
            'data' => $company
        ], 200);

    }
    //Função para chamar um empresa pelo seu cnpj
    #[Route('/companys/getByCnpj', name: 'company_get_bycnpj', methods: ['GET'])]
    public function getByCnpj(Request $request, CompanyRepository $companyRepository, SerializerInterface $serializer): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        $Partnerscompany = $company->getPartners();

        $companyData = [
            'nomeFantasia'=> $company->getNomeFantasia(),
            'cnpj' => $company->getCnpj()
        ];


        $PartnersData = [];
        foreach ($Partnerscompany as $Partnercompany) {
            $PartnersData[] = [
                'partners' => [
                    'nome' =>$Partnercompany->getPartner()->getNome(),
                    'cpf' => $Partnercompany->getPartner()->getCpf(),
                ],
                'percent' => $Partnercompany->getPercent()
            ];
        }
        $data = [
            "company" => $companyData,
            'partners' => $PartnersData
        ];
   
        return $this->json([
            'message' => 'company Found',
            'data' => $data
        ],200);
    }
       //Função para chamar um empresa pelo seu Nome
       #[Route('/companys/getByNomeFantasia', name: 'company_get_byNomeFantasia', methods: ['GET'])]
       public function getByNomeFantasia(Request $request, CompanyRepository $companyRepository): JsonResponse
       {
           if($request -> headers->get('Content-Type') == 'application/json'){
               $data = $request->toArray();
           }else{
               $data = $request->request->all();
           }
           $company = $companyRepository->findOneByNomeFantasia($data['nomeFantasia']);
           if(!$company) throw new \Exception('company was not found');
           
           $data = $company->formatarResponseCompany();
      
           return $this->json([
               'message' => 'company Found',
               'data' => $data
               
           ],200);
       }
    //Função para adicionar um socio a uma empresa
    #[Route('/companys/addpartner', name: 'partner_addcompany', methods: ['PATCH'])]
    public function addPartner( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, PartnerRepository $PartnerRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        
        $Partner = $PartnerRepository->findOneByCpf($data['cpf']);
        if(!$Partner) throw new \Exception('Partner was not found');

        if ($company->verificarPartner($Partner)) {
            throw new \Exception('Partner is not associated with this company.');
        }
        

        $company->addPartner($Partner, $data['percent']);
        $d->getManager() ->flush();


        return $this->json([
            'message' => 'Partner added successfully',
            
        ],200);
    }
    //Função para deletar um sócio de uma empresa pelo seu cpf
    #[Route('/companys/deletePartner', name: 'Partner_delete', methods: ['DELETE'])]
    public function removePartner( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, PartnerRepository $PartnerRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        
        $Partner = $PartnerRepository->findOneByCpf($data['cpf']);
        if(!$Partner) throw new \Exception('Partner was not found');

        if (!$company->verificarPartner($Partner)) {
            throw new \Exception('Partner is not associated with this company.');
        }

        $company->removerPartner($Partner);
        $d->getManager() ->flush();


        return $this->json([
            'message' => 'Partner deleted successfully',
            'data' => $Partner
            
        ],200);
    }
    //Função para chamar todos os socios de uma empresa
    #[Route('/companys/getPartnerByCnpj', name: 'company_get_Partner_bycnpj', methods: ['GET'])]
    public function getPartnersByCnpj(Request $request, CompanyRepository $companyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        
        $data = $company->formatarResponseCompany();

        return $this->json([
            'data' => $data
            
        ],200);


    }
    //Atualiza a porcetagem de um sócio
    #[Route('/companys/updatePercent', name: 'Partner_addcompany', methods: ['PATCH'])]
    public function updatePercent( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, PartnerRepository $PartnerRepository, PartnerCompanyRepository $partnerCompanyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        
        $Partner = $PartnerRepository->findOneByCpf($data['cpf']);
        if(!$Partner) throw new \Exception('Partner was not found');

        if ($company->verificarPartner($Partner)) {
            throw new \Exception('Partner is associated with this company.');
        }


        $company->addPartner($Partner, $data['percent']);
        $Partner->addCompany($company, $data['percent']);
        $d->getManager()->flush();
        
    

        return $this->json([
            'message' => 'Partner percent updated successfully',
        ], 200);

    }

    


}

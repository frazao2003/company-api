<?php

namespace App\Controller;

use App\Entity\company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Validator;


class companyController extends AbstractController
{
    #[Route('/companys', name: 'app_company', methods: ['GET'])]
    public function getAll(CompanyRepository $companyRepository): JsonResponse
    {
        return $this->json([
            'data' => $companyRepository->findAll(),
        ],200);
    }
    #[Route('/companys', name: 'company_create', methods: ['POST'])]
    public function create(Request $request, CompanyRepository $companyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

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

    #[Route('/companys/{company}', name: 'company_single', methods: ['GET'])]
    public function getSingle(int $company, CompanyRepository $companyRepository): JsonResponse
    {
        $company = $companyRepository->find($company);
        if(!$company) throw new \Exception('company was not found');
        
        return $this->json([
            'data' => $company,
        ],200);
    }
    #[Route('/companys/{company}', name: 'company_update', methods: ['PUT', 'PATCH'])]
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
            'data' => $company
        ], 201);
    }
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
    #[Route('/companys/getByCnpj', name: 'company_get_bycnpj', methods: ['GET'])]
    public function getByCnpj(Request $request, CompanyRepository $companyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        $company = $companyRepository->findOneByCpf($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
   
        return $this->json([
            'message' => 'company Found',
            'data' => $company
            
        ],200);
    }

    #[Route('/companys/addPartner', name: 'Partner_addcompany', methods: ['PATCH'])]
    public function addPartner( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, PartnerRepository $PartnerRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        
        $company = $companyRepository->findOneByCpf($data['cnpj']);
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
            'data' => $Partner
            
        ],200);
    }
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
        
        $company = $companyRepository->findOneByCpf($data['cnpj']);
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

    #[Route('/companys/getPartnerByCnpj', name: 'company_get_Partner_bycnpj', methods: ['GET'])]
    public function getPartnersByCnpj(Request $request, CompanyRepository $companyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        $company = $companyRepository->findOneByCpf($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');

        $Partnerscompany = $company->getPartners();

        $PartnersData = [];
        foreach ($Partnerscompany as $Partnercompany) {
            $PartnersData[] = [
                'Partner' => $Partnercompany->getPartner(),
                'percent' => $Partnercompany->getPercent(),
            ];
        }

        return $this->json([
            'data' => $PartnersData
            
        ],200);


    }
    


}

<?php

namespace App\Controller;

use App\Entity\company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CompanyRepository;
use App\Repository\SocioRepository;
use \Exception;
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

        foreach ($company->getSocios() as $sociocompany) {
            $doctrine->getManager()->remove($sociocompany);
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

    #[Route('/companys/addsocio', name: 'socio_addcompany', methods: ['PATCH'])]
    public function addSocio( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, SocioRepository $socioRepository): JsonResponse
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
        
        $socio = $socioRepository->findOneByCpf($data['cpf']);
        if(!$socio) throw new \Exception('Socio was not found');

        if ($company->verificarSocio($socio)) {
            throw new \Exception('Partner is not associated with this company.');
        }
        

        $company->addSocio($socio, $data['percent']);
        $d->getManager() ->flush();


        return $this->json([
            'message' => 'Socio added successfully',
            'data' => $socio
            
        ],200);
    }
    #[Route('/companys/deletesocio', name: 'socio_delete', methods: ['DELETE'])]
    public function removeSocio( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, SocioRepository $socioRepository): JsonResponse
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
        
        $socio = $socioRepository->findOneByCpf($data['cpf']);
        if(!$socio) throw new \Exception('Socio was not found');

        if (!$company->verificarSocio($socio)) {
            throw new \Exception('Partner is not associated with this company.');
        }

        $company->removerSocio($socio);
        $d->getManager() ->flush();


        return $this->json([
            'message' => 'Socio deleted successfully',
            'data' => $socio
            
        ],200);
    }

    #[Route('/companys/getByCnpj', name: 'company_get_bycnpj', methods: ['GET'])]
    public function getsociosByCnpj(Request $request, CompanyRepository $companyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        $company = $companyRepository->findOneByCpf($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');

        $socioscompany = $company->getSocios();

        $sociosData = [];
        foreach ($socioscompany as $sociocompany) {
            $sociosData[] = [
                'socio' => $sociocompany->getSocio(),
                'percent' => $sociocompany->getPercent(),
            ];
        }

        return $this->json([
            'data' => $sociosData
            
        ],200);


    }
    


}

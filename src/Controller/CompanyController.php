<?php

namespace App\Controller;

use App\Dto\CompanyFilter;
use App\Dto\CompanyDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CompanyService;

class CompanyController extends AbstractController
{
    private CompanyService $companyService;
    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    #[Route('/companys', name: 'app_company', methods: ['GET'])]
    public function filterCompanies(Request $request): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $filter = new CompanyFilter();
        $filter->setName($data['name']);
        $filter->setCnpj($data['cnpj']);
        $data = $this->companyService->filterCompanies($filter);
        return $this->json(['data' => $data], 200);
        
    }
    //Cadastrar um empresa
    #[Route('/companys', name: 'company_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        //validar o tipos de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $companyDTO = new CompanyDTO(
            $data['name'],
            $data['cnpj']
        );
        $company = $this->companyService->create($companyDTO);
        return $this->json([
            'message' => 'company Created Successfully',
            'data' => $company
        ], 201);
    }

    //Atualizar uma empresa
    #[Route('/companys/{id}', name: 'company_update', methods: ['PUT'])]
    public function update(Request $request, int $id): JsonResponse
    {
  
        //validar o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $companyDTO = new CompanyDTO(
            $data['name'],
            $data['cnpj']
        );
        $data = $this->companyService->update($id, $companyDTO);
        return $this->json([
            'message' => 'company Updated Successfully',
            'data' => $data
        ], 201);
    }
    //Deletar uma empresa
    #[Route('/companys/{id}', name: 'company_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {      
        $data = $this->companyService->delete($id);
        return $this->json([
            'message' => 'company deleted Successfully',
            'data' => $data
        ], 200);

    }
    //Função para chamar um empresa pelo seu id
    #[Route('/companys/{id}', name: 'company_get_bycnpj', methods: ['GET'])]
    public function getByid(int $id): JsonResponse
    {
        $data = $this->companyService->getById($id);
        return $this->json([
            'message' => 'company Found',
            'data' => $data
        ],200);
    }
       
    //Função para adicionar um socio a uma empresa
    #[Route('/companys/addpartner', name: 'partner_addcompany', methods: ['POST'])]
    public function addPartner( Request $request): JsonResponse
    {
        //validar tipos de dados do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $cnpj = $data['cnpj'];
        $cpf = $data['cpf'];
        $percent = $data['percent'];
        $data = $this->companyService->addPartner($cnpj, $cpf, $percent);
        return $this->json([
            'message' => 'Partner added successfully',
            'data' => $data
            
        ],200);
    }
    //Função para deletar um sócio de uma empresa pelo seu cpf
    #[Route('/companys/deletePartner', name: 'Partner_delete', methods: ['DELETE'])]
    public function removePartner( Request $request): JsonResponse
    {
        //validar tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $cnpj = $data['cnpj'];
        $cpf = $data['cpf'];
        $data = $this->companyService->removePartner($cnpj, $cpf);
        return $this->json([
            'message' => 'Partner deleted successfully',
            'data' => $data
            
        ],200);
    }
    //Atualiza a porcetagem de um sócio
    #[Route('/companys/updatePercent', name: 'Partner_addcompany', methods: ['PATCH'])]
    public function updatePercent( Request $request): JsonResponse
    {
        //valida tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $cnpj = $data['cnpj'];
        $cpf = $data['cpf'];
        $percent = $data['percent'];
        $data = $this->companyService->updatePercent($cnpj, $cpf, $percent);

        return $this->json([
            'message' => 'Partner percent updated successfully',
            'data' => $data
        ], 200);

    }

    


}

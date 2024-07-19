<?php

namespace App\Controller;

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
    public function getAll(): JsonResponse
    {
        $data = $this->companyService->getAll();
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
        $cnpj = $data['cnpj'];
        $nomeFantasia = $data['nomeFantasia'];
        $company = $this->companyService->create($cnpj, $nomeFantasia);
        return $this->json([
            'message' => 'company Created Successfully',
            'data' => $company
        ], 201);
    }

    //Atualizar uma empresa
    #[Route('/companys', name: 'company_update', methods: ['PUT'])]
    public function update(Request $request): JsonResponse
    {
  
        //validar o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $id = $data['id'];
        $cnpj = $data['cnpj'];
        $nomeFantasia = $data['nomeFantasia'];
        $data = $this->companyService->update($id, $cnpj,$nomeFantasia);
        return $this->json([
            'message' => 'company Updated Successfully',
            'data' => $data
        ], 201);
    }
    //Deletar uma empresa
    #[Route('/companys', name: 'company_delete', methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse
    {
        //validar o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }        
        $cnpj = $data['cnpj'];
        $data = $this->companyService->delete($cnpj);
        return $this->json([
            'message' => 'company deleted Successfully',
            'data' => $data
        ], 200);

    }
    //Função para chamar um empresa pelo seu cnpj
    #[Route('/companys/getByCnpj', name: 'company_get_bycnpj', methods: ['GET'])]
    public function getByCnpj(Request $request): JsonResponse
    {
        //validar tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $cnpj = $data['cnpj'];
        $data = $this->companyService->getByCnpj($cnpj);
        return $this->json([
            'message' => 'company Found',
            'data' => $data
        ],200);
    }
       //Função para chamar um empresa pelo seu Nome
       #[Route('/companys/getByNomeFantasia', name: 'company_get_byNomeFantasia', methods: ['GET'])]
       public function getByNomeFantasia(Request $request): JsonResponse
       {
           //validar tipo de dado do request    
           if($request -> headers->get('Content-Type') == 'application/json'){
               $data = $request->toArray();
           }else{
               $data = $request->request->all();
           }
           $nomeFantasia = $data['nomefantasia'];
           $data = $this->companyService->getByNomeFantasia($nomeFantasia);
           return $this->json([
               'message' => 'company Found',
               'data' => $data
               
           ],200);
       }
    //Função para adicionar um socio a uma empresa
    #[Route('/companys/addpartner', name: 'partner_addcompany', methods: ['PATCH'])]
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

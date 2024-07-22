<?php

namespace App\Controller;
use App\Dto\PartnerFilter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\PartnerService;



class PartnerController extends AbstractController
{   
    //Injeção de dependências
    private PartnerService $partnerService;
    public function __construct(PartnerService $partnerService)
    {
        $this->partnerService = $partnerService;
    }
    //função para buscar todos os partner 
    #[Route('/partner', name: 'app_partner', methods: ['GET'])]
    public function filterPartner(PartnerFilter $filter): JsonResponse
    {
        $data = $this->partnerService->filterPartner($filter);
        return $this->json([
            'data' => $data
        ],200);
    }
    //criar partner
    #[Route('/partner', name: 'partner_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        //verifica o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        $cpf = $data['cpf'];
        $nome =$data['nome'];
        $partner = $this->partnerService->create($cpf, $nome);
        return $this->json([
            'message' => 'partner Created Successfully',
            'data' => $partner
        ], 201);
    }
    //busca um partner pelo seu cpg
    #[Route('/partner/{id}', name: 'partner_single', methods: ['GET'])]
    public function getSingle(int $id): JsonResponse
    {
        $data = $this->partnerService->getById($id);
        return $this->json([
            'data' => $data,
        ],200);
    }
    //Atualiza um sócio pelo seu id
    #[Route('/partner/update/{id}', name: 'partner_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        //verfica o tidpo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $nome = $data['nome'];
        $cpf = $data['cpf'];
        $partner = $this->partnerService->update($id, $nome, $cpf);
        return $this->json([
            'message' => 'partner Updated Successfully',
            'data' => $partner
        ], 201);
    } 
    //Deleta um sócio
    #[Route('/partner/delete/{id}', name: 'partner_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): JsonResponse
    {
        
        $partner = $this->partnerService->delete($id);
        return $this->json([
            'message' => 'partner deleted Successfully',
            'data' => $partner
        ], 200);

    }

   
         
    
}

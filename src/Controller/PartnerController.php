<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Exception;
use App\Repository\PartnerRepository;
use App\Entity\Partner;
use App\Repository\PartnerCompanyRepository;
use App\Service\PartnerService;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Validator;


class PartnerController extends AbstractController
{   
    //Injeção de dependências
    private PartnerService $partnerService;
    //função para buscar todos os partner 
    #[Route('/partner', name: 'app_partner', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $data = $this->partnerService->getAll();
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
    #[Route('/partner/getbyCpf', name: 'partner_single', methods: ['GET'])]
    public function getSingle(Request $request): JsonResponse
    {
        //verifica o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        $cpf = $data['cpf'];
        $data = $this->partnerService->getByCpf($cpf);
        return $this->json([
            'data' => $data,
        ],200);
    }
    //Atualiza um sócio pelo seu id
    #[Route('/partner/update', name: 'partner_update', methods: ['PUT'])]
    public function update(int $partner, Request $request): JsonResponse
    {
        //verfica o tidpo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $id = $data['id'];
        $nome = $data['nome'];
        $cpf = $data['cpf'];
        $partner = $this->partnerService->update($id, $nome, $cpf);
        return $this->json([
            'message' => 'partner Updated Successfully',
            'data' => $partner
        ], 201);
    } 
    //Deleta um sócio
    #[Route('/partner/deleteByCPF', name: 'partner_delete', methods: ['DELETE'])]
    public function delete(Request $request): JsonResponse
    {
        //valida o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $cpf = $data['cpf'];
        $partner = $this->partnerService->delete($cpf);
        return $this->json([
            'message' => 'partner deleted Successfully',
            'data' => $partner
        ], 200);

    }

   
         
    
}

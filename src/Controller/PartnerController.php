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
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Validator;


class PartnerController extends AbstractController
{
    //função para buscar todos os partner 
    #[Route('/partner', name: 'app_partner', methods: ['GET'])]
    public function getAll(PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): JsonResponse
    {
        //Chama todos os partner do banco de dados
        $partners = $partnerRepository->findAll();
        $data = [];
        //formata os dados do response
        foreach($partners as $partner){
            $partnerData  = [
                'nome' =>$partner->getNome(),
                'cpf' =>$partner->getCpf()
            ];
            $partnerCompanies = $partnerCompanyRepository->findAllByPartner($partner);
            $companyData = [];
            foreach($partnerCompanies as $partnerCompany){
                $companyData = [
                    'nomeFantasia'=> $partnerCompany->getCompany()->getNomeFantasia(),
                    'cnpj' => $partnerCompany->getCompany()->getCnpj(),
                    'percent' => $partnerCompany->getPercent()
                ];
            }
            $data [] = [
                'partner'=> $partnerData,
                'company' => $companyData
            ];
        }

        return $this->json([
            'data' => $data
        ],200);
    }
    //criar partner
    #[Route('/partner', name: 'partner_create', methods: ['POST'])]
    public function create(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        //verifica o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        //valida os campos do request
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');

        if(!Validator::isOnlyLettersAndSpaces($data['nome'])) throw new Exception('This field can only have letters');
        //inicia um novo partner 
        $partner = new Partner();
        $partner->setNome($data['nome']);
        $partner->setCpf($data['cpf']);
        $partner->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $partner->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        //persiste os dados
        $partnerRepository->add($partner, true);
        return $this->json([
            'message' => 'partner Created Successfully',
            'data' => $partner
        ], 201);
    }
    //busca um partner pelo seu cpg
    #[Route('/partner/getbyCpf', name: 'partner_single', methods: ['GET'])]
    public function getSingle(Request $request, PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): JsonResponse
    {
        //verifica o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        //valida i cof
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');
        //busca no banco e valida a existência
        $partner = $partnerRepository->findOneByCpf($data['cpf']);
        if(!$partner) throw new Exception('partner was not found');
        //formata o response
        $data = $partner->formataCompanyResponse($partnerCompanyRepository);
        
        return $this->json([
            'data' => $data,
        ],200);
    }
    //Atualiza um sócio pelo seu id
    #[Route('/partner/update', name: 'partner_update', methods: ['PUT'])]
    public function update(int $partner, Request $request, ManagerRegistry $d, PartnerRepository $partnerRepository): JsonResponse
    {
        //verfica o tidpo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        //busca o partner pelo id e valida a existência
        $partner = $partnerRepository->find($data['id']);
        if(!$partner) throw new Exception('partner was not found');
        $partner->setNome($data['nome']);
        //valida o cpf
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        $partner->setCpf($data['cpf']);
        $d->getManager() ->flush();
        return $this->json([
            'message' => 'partner Updated Successfully',
            'data' => $partner
        ], 201);
    }
    //Deleta um sócio
    #[Route('/partner/deleteByCPF', name: 'partner_delete', methods: ['DELETE'])]
    public function delete(Request $request, PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository, ManagerRegistry $doctrine): JsonResponse
    {
        //valida o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        //busca no banco de dados e valida existência
        $partner = $partnerRepository->findOneByCpf($data['cpf']);
        if(!$partner) throw new Exception('partner was not found');
        //busca todos os partnerCompany associados a esse ártner
        $partnerCompanies = $partnerCompanyRepository->findAllByPartner($partner);
        //deleta todos e atualiza o percent da company associada
        foreach($partnerCompanies as $partnerCompany){
            $company = $partnerCompany->getCompany();
            $company->setPercent($company->getPercent() + $partnerCompany->getPercent());
            $doctrine->getManager()->remove($partnerCompany);
            $doctrine->getManager()->persist($company);
            $doctrine->getManager()->flush();

        }
        //remove o partner
        $partnerRepository->remove($partner, true);


        return $this->json([
            'message' => 'partner deleted Successfully',
            'data' => $partner
        ], 200);

    }

   
         
    
}

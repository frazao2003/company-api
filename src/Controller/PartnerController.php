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
    #[Route('/partner', name: 'app_partner', methods: ['GET'])]
    public function getAll(PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): JsonResponse
    {
        $partners = $partnerRepository->findAll();
        $data = [];
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
    #[Route('/partner', name: 'partner_create', methods: ['POST'])]
    public function create(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');

        if(!Validator::isOnlyLettersAndSpaces($data['nome'])) throw new Exception('This field can only have letters');

        $partner = new Partner();
        $partner->setNome($data['nome']);
        $partner->setCpf($data['cpf']);
        $partner->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $partner->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $partnerRepository->add($partner, true);
        return $this->json([
            'message' => 'partner Created Successfully',
            'data' => $partner
        ], 201);
    }

    #[Route('/partner/getbyCpf', name: 'partner_single', methods: ['GET'])]
    public function getSingle(Request $request, PartnerRepository $partnerRepository, PartnerCompanyRepository $partnerCompanyRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');
        $partner = $partnerRepository->findOneByCpf($data['cpf']);
        if(!$partner) throw new Exception('partner was not found');
        $data = $partner->formataCompanyResponse($partnerCompanyRepository);
        
        return $this->json([
            'data' => $data,
        ],200);
    }
    //Atualiza um sócio pelo seu id
    #[Route('/partner/update', name: 'partner_update', methods: ['PUT', 'PATCH'])]
    public function update(int $partner, Request $request, ManagerRegistry $d, PartnerRepository $partnerRepository): JsonResponse
    {
        
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $partner = $partnerRepository->find($data['id']);
        if(!$partner) throw new Exception('partner was not found');
        $partner->setNome($data['nome']);
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
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        $partner = $partnerRepository->findOneByCpf($data['cpf']);
        if(!$partner) throw new Exception('partner was not found');
        $partnerCompanies = $partnerCompanyRepository->findAllByPartner($partner);
        foreach($partnerCompanies as $partnerCompany){
            $company = $partnerCompany->getCompany();
            $company->setPercent($company->getPercent() + $partnerCompany->getPercent());
            $doctrine->getManager()->remove($partnerCompany);
            $doctrine->getManager()->persist($company);
            $doctrine->getManager()->flush();

        }

        $partnerRepository->remove($partner, true);


        return $this->json([
            'message' => 'partner deleted Successfully',
            'data' => $partner
        ], 200);

    }

   
         
    
}

<?php

namespace App\Controller;
use App\Entity\Companie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CompanieRepository;
use Exception;
use App\Repository\partnerRepository;
use App\Entity\Partner;
use App\Repository\partnerCompaineRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Validator;


class partnerController extends AbstractController
{
    #[Route('/partner', name: 'app_partner', methods: ['GET'])]
    public function getAll(partnerRepository $partnerRepository): JsonResponse
    {
        return $this->json([
            'data' => $partnerRepository->findAll(),
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

    #[Route('/partner/{partner}', name: 'partner_single', methods: ['GET'])]
    public function getSingle(int $partner, PartnerRepository $partnerRepository): JsonResponse
    {
        $partner = $partnerRepository->find($partner);
        if(!$partner) throw new Exception('partner was not found');
        
        return $this->json([
            'data' => $partner,
        ],200);
    }
    #[Route('/partner/{partner}', name: 'partner_update', methods: ['PUT', 'PATCH'])]
    public function update(int $partner, Request $request, ManagerRegistry $d, PartnerRepository $partnerRepository): JsonResponse
    {
        
        $partner = $partnerRepository->find($partner);
        if(!$partner) throw new Exception('partner was not found');
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        $partner->setNome($data['nome']);
        $partner->setCpf($data['cpf']);
        $d->getManager() ->flush();
        return $this->json([
            'message' => 'partner Updated Successfully',
            'data' => $partner
        ], 201);
    }
    #[Route('/partner/{partner}', name: 'partner_delete', methods: ['DELETE'])]
    public function delete(int $partner, PartnerRepository $partnerRepository): JsonResponse
    {
        $partner = $partnerRepository->find($partner);
        if(!$partner) throw new Exception('partner was not found');

        $partnerRepository->remove($partner, true);


        return $this->json([
            'message' => 'partner deleted Successfully',
            'data' => $partner
        ], 200);

    }
    #[Route('/partner/getByCpf', name: 'partner_get_bycpf', methods: ['GET'])]
    public function getByCpf(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');
        $partner = $partnerRepository->findOneByCpf($data['cpf']);
        if(!$partner) throw new Exception('partner was not found');


        return $this->json([
            'message' => 'partner found',
            'data' => $partner
        ],200);
    }

    #[Route('/partner/getCompanyByCpf', name: 'partner_get_company_bycpf', methods: ['GET'])]
    public function getCompanyByCpf(Request $request, PartnerRepository $partnerRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');
        $partner = $partnerRepository->findOneByCpf($data['cpf']);
        if(!$partner) throw new Exception('partner was not found');

        $partnersCompany = $partner->getCompany();

        $companyData = [];
        foreach ($partnersCompany as $partnerCompany) {
            $partnersData[] = [
                'company' => $partnerCompany->getCompany(),
            ];
        }


        return $this->json([
            'message' => 'partner found',
            'data' => $companyData
        ],200);
    }

   
         
    
}

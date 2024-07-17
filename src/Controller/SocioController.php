<?php

namespace App\Controller;
use App\Entity\Companie;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CompanieRepository;
use Exception;
use App\Repository\SocioRepository;
use App\Entity\Socio;
use App\Entity\SocioCompanie;
use App\Repository\SocioCompaineRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Validator;


class SocioController extends AbstractController
{
    #[Route('/socio', name: 'app_Socio', methods: ['GET'])]
    public function getAll(SocioRepository $SocioRepository): JsonResponse
    {
        return $this->json([
            'data' => $SocioRepository->findAll(),
        ],200);
    }
    #[Route('/socio', name: 'Socio_create', methods: ['POST'])]
    public function create(Request $request, SocioRepository $SocioRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');

        if(!Validator::isOnlyLettersAndSpaces($data['nome'])) throw new Exception('This field can only have letters');

        $Socio = new Socio();
        $Socio->setNome($data['nome']);
        $Socio->setCpf($data['cpf']);
        $Socio->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $Socio->setUpdatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $SocioRepository->add($Socio, true);
        return $this->json([
            'message' => 'Socio Created Successfully',
            'data' => $Socio
        ], 201);
    }

    #[Route('/socio/{socio}', name: 'Socio_single', methods: ['GET'])]
    public function getSingle(int $Socio, SocioRepository $SocioRepository): JsonResponse
    {
        $Socio = $SocioRepository->find($Socio);
        if(!$Socio) throw new Exception('Socio was not found');
        
        return $this->json([
            'data' => $Socio,
        ],200);
    }
    #[Route('/socio/{socio}', name: 'Socio_update', methods: ['PUT', 'PATCH'])]
    public function update(int $Socio, Request $request, ManagerRegistry $d, SocioRepository $SocioRepository): JsonResponse
    {
        
        $Socio = $SocioRepository->find($Socio);
        if(!$Socio) throw new Exception('Socio was not found');
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        $Socio->setNome($data['nome']);
        $Socio->setCpf($data['cpf']);
        $d->getManager() ->flush();
        return $this->json([
            'message' => 'Socio Updated Successfully',
            'data' => $Socio
        ], 201);
    }
    #[Route('/socio/{Socio}', name: 'socio_delete', methods: ['DELETE'])]
    public function delete(int $Socio, SocioRepository $SocioRepository): JsonResponse
    {
        $Socio = $SocioRepository->find($Socio);
        if(!$Socio) throw new Exception('Socio was not found');

        $SocioRepository->remove($Socio, true);


        return $this->json([
            'message' => 'Socio deleted Successfully',
            'data' => $Socio
        ], 200);

    }
    #[Route('/socio/getByCpf', name: 'socio_get_bycpf', methods: ['GET'])]
    public function getByCpf(Request $request, SocioRepository $SocioRepository): JsonResponse
    {
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        if(!Validator::validarCPF($data['cpf'])) throw new Exception('CPF inválido');
        $Socio = $SocioRepository->findOneByCpf($data['cpf']);
        if(!$Socio) throw new Exception('Socio was not found');


        return $this->json([
            'message' => 'Socio found',
            'data' => $Socio
        ],200);
    }

   
         
    
}

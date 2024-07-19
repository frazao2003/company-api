<?php

namespace App\Controller;

use App\Entity\Company;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\CompanyRepository;
use App\Repository\PartnerRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utils\Validator;
use App\Entity\PartnerCompany;
use App\Repository\PartnerCompanyRepository;
use Symfony\Component\Serializer\SerializerInterface;

class CompanyController extends AbstractController
{
    #[Route('/companys', name: 'app_company', methods: ['GET'])]
    public function getAll(CompanyRepository $companyRepository): JsonResponse
    {
        $companies = $companyRepository->findAll();
        $companiesData = [];
        $data = [];
        //Formatar o array de company
        foreach($companies as $company){
            $companiesData []= [
                'nomeFantasia' => $company->getNomeFantasia(),
                'cnpj' => $company->getCnpj(),
                'percent' => $company->getPercent()
            ];
            $partnercompany = $company->getPartners();
            $partnerData = [];
            foreach($partnercompany as $partnercompany){
                $partnerData [] = [
                    'nome' => $partnercompany->getPartner()->getNome(),
                    'cpf' => $partnercompany->getPartner()->getCpf(),
                    'percent' => $partnercompany->getPercent()
                ];
            }
            $data[] = [
                'company' => $companiesData,
                'partners' => $partnerData
            ];
            $companiesData = [];
        }
        return $this->json(['data' => $data], 200);
        
    }
    //Cadastrar um empresa
    #[Route('/companys', name: 'company_create', methods: ['POST'])]
    public function create(Request $request, CompanyRepository $companyRepository): JsonResponse
    {
        //validar o tipos de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();

        }else{
            $data = $request->request->all();

        }
        //validar o conteúdo do array
        if (!array_key_exists('cnpj', $data)) {
            throw new \Exception('CNPJ is missing');
        }
        
        if (!array_key_exists('nomeFantasia', $data)) {
            throw new \Exception('Nome Fantasia is missing');
        }

        //validar o cnpj
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        
        // validar se o cnpj já não está cadastrado
        if ($companyRepository->existsByCnpj($data['cnpj'])) {
            throw new \Exception('The company CNPJ is already registered ');
        } 
        //criar nova entidade Company
        $company = new Company();
        $company->setNomeFantasia($data['nomeFantasia']);
        $company->setCnpj($data['cnpj']);
        $company->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone('America/Sao_Paulo')));
        $company->setPercent(100);
        //persistir os dados no banco de dados
        $companyRepository->add($company, true);
        return $this->json([
            'message' => 'company Created Successfully',
            'data' => $company
        ], 201);
    }

    //Atualizar uma empresa
    #[Route('/companys', name: 'company_update', methods: ['PUT'])]
    public function update(Request $request, ManagerRegistry $d, CompanyRepository $companyRepository): JsonResponse
    {
  
        //validar o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        // buscar a company pelo id
        $company = $companyRepository->find($data['id']);
        //validar se a company existe no banco de dados
        if(!$company) throw new \Exception('company was not found');        
        //validar o conteúdo do array
        if (!array_key_exists('cnpj', $data)) {
            throw new \Exception('CNPJ is missing');
        }
        
        if (!array_key_exists('nomeFantasia', $data)) {
            throw new \Exception('Nome Fantasia is missing');
        }

        //validar o cnpj
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        
        // validar se o cnpj já não está cadastrado
        if ($companyRepository->existsByCnpj($data['cnpj'])) {
            throw new \Exception('The company CNPJ is already registered ');
        }     
        //atualizar dados
        $company->setNomeFantasia($data['nomeFantasia']);
        $company->setCnpj($data['cnpj']);
        // persistir dados
        $d->getManager() ->flush();
        $data = $company->formatarResponseCompany();
        return $this->json([
            'message' => 'company Updated Successfully',
            'data' => $data
        ], 201);
    }
    //Deletar uma empresa
    #[Route('/companys', name: 'company_delete', methods: ['DELETE'])]
    public function delete(Request $request, CompanyRepository $companyRepository, ManagerRegistry $doctrine): JsonResponse
    {
        //validar o tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }        
        //buscar a company pelo id
        $company = $companyRepository->find($data['id']);
        //validar se a company existe no banco de dados
        if(!$company) throw new \Exception('company was not found');
        //deletar todos os partner associados 
        foreach ($company->getPartners() as $Partnercompany) {
            $doctrine->getManager()->remove($Partnercompany);
        }
        //persistir dados no banco de dados
        $companyRepository->remove($company, true);
        //formatar para response
        $data = $company->formatarResponseCompany();


        return $this->json([
            'message' => 'company deleted Successfully',
            'data' => $data
        ], 200);

    }
    //Função para chamar um empresa pelo seu cnpj
    #[Route('/companys/getByCnpj', name: 'company_get_bycnpj', methods: ['GET'])]
    public function getByCnpj(Request $request, CompanyRepository $companyRepository, SerializerInterface $serializer): JsonResponse
    {
        //validar tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        //validar CNPJ
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        //buscar a company pelo CNPJ
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        //validar a existência
        if(!$company) throw new \Exception('company was not found');
        //formatar para response
        $data = $company->formatarResponseCompany();
     
        return $this->json([
            'message' => 'company Found',
            'data' => $data
        ],200);
    }
       //Função para chamar um empresa pelo seu Nome
       #[Route('/companys/getByNomeFantasia', name: 'company_get_byNomeFantasia', methods: ['GET'])]
       public function getByNomeFantasia(Request $request, CompanyRepository $companyRepository): JsonResponse
       {
           //validar tipo de dado do request    
           if($request -> headers->get('Content-Type') == 'application/json'){
               $data = $request->toArray();
           }else{
               $data = $request->request->all();
           }
           //buscar company pelo nomeFantasisa
           $company = $companyRepository->findOneByNomeFantasia($data['nomeFantasia']);
           //validar existência
           if(!$company) throw new \Exception('company was not found');
           
           //formatar para response
           $data = $company->formatarResponseCompany();
      
           return $this->json([
               'message' => 'company Found',
               'data' => $data
               
           ],200);
       }
    //Função para adicionar um socio a uma empresa
    #[Route('/companys/addpartner', name: 'partner_addcompany', methods: ['PATCH'])]
    public function addPartner( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, PartnerRepository $PartnerRepository, PartnerCompanyRepository $partnerCompanyRepository ): JsonResponse
    {
        //validar tipos de dados do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        //validar CPF e CNPJ
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        
        //buscar company pelo CNPJ
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        //buscar partner pelo CPF
        $Partner = $PartnerRepository->findOneByCpf($data['cpf']);
        if(!$Partner) throw new \Exception('Partner was not found');
        // validar se o partner já está associado com a company
        if ($company->verificarPartner($Partner)) {
            throw new \Exception('Partner is not associated with this company.');
        }
        //validar se a porcentagem está disponível na company
        if($company->getPercent() < $data['percent']){
            throw new \Exception('The percentage exceeds the available percentage of the company.');
        }


        //persistir dados
        $company->addPartner($Partner, $data['percent']);
        $company->setPercent($company->getPercent() - $data['percent']);
        $d->getManager() ->flush();
        //formatar response
        $data = $company->formatarResponseCompany();

        return $this->json([
            'message' => 'Partner added successfully',
            'data' => $data
            
        ],200);
    }
    //Função para deletar um sócio de uma empresa pelo seu cpf
    #[Route('/companys/deletePartner', name: 'Partner_delete', methods: ['DELETE'])]
    public function removePartner( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, PartnerRepository $PartnerRepository, PartnerCompanyRepository $partnerCompanyRepository): JsonResponse
    {
        //validar tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        //validar CPF e CNPJ
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        
        //buscar company pelo CNPJ
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        
        //buscar partner pelo CPF
        $Partner = $PartnerRepository->findOneByCpf($data['cpf']);
        if(!$Partner) throw new \Exception('Partner was not found');
        //validar se o partner está relacionado a company
        $partnerCompany = $partnerCompanyRepository->findOneBy(['company' => $company, 'Partner' => $Partner]);
        if (!$partnerCompany) {
            throw new \Exception('Partner is not associated with this company.');
        }
         
        //persistir dados
        $em = $d->getManager();
        $em->remove($partnerCompany);
        $company->setPercent($company->getPercent() + $partnerCompany->getPercent());
        $em->flush();
        //formatar response
        $data = $company->formatarResponseCompany();


        return $this->json([
            'message' => 'Partner deleted successfully',
            'data' => $data
            
        ],200);
    }
    //Atualiza a porcetagem de um sócio
    #[Route('/companys/updatePercent', name: 'Partner_addcompany', methods: ['PATCH'])]
    public function updatePercent( Request $request, ManagerRegistry $d, CompanyRepository $companyRepository, PartnerRepository $PartnerRepository, PartnerCompanyRepository $partnerCompanyRepository): JsonResponse
    {
        //valida tipo de dado do request
        if($request -> headers->get('Content-Type') == 'application/json'){
            $data = $request->toArray();
        }else{
            $data = $request->request->all();
        }
        //valida cpf e cnpj
        if(!Validator::validarCNPJ($data['cnpj'])) throw new \Exception('CNPJ inválido');
        if(!Validator::validarCPF($data['cpf'])) throw new \Exception('CPF inválido');
        
        //busca e valida se a company existe
        $company = $companyRepository->findOneByCnpj($data['cnpj']);
        if(!$company) throw new \Exception('company was not found');
        //busca e valida se a company existe
        $Partner = $PartnerRepository->findOneByCpf($data['cpf']);
        if(!$Partner) throw new \Exception('Partner was not found');
        // valida a porcentagem disponível na company
        if($company->getPercent() < $data['percent']){
            throw new \Exception('The percentage exceeds the available percentage of the company.');
        }
        //valida se o partner está associado a company
        $partnercompany = $company->getPartnerCompany($Partner);
        if (!$partnercompany) {
            throw new \Exception('Partner isnt associated with this company.');
        }
        //calcula novo percentual da company pós att
        if($partnercompany->getPercent() < $data['percent']){
            $percentCompany =$company->getPercent() - ($data['percent'] - $partnercompany->getPercent());
            $company->setPercent($percentCompany);
        }if($partnercompany->getPercent() > $data['percent']){
            $percentCompany = $company->getPercent() + ($partnercompany->getPercent() - $data['percent']);
            $company->setPercent($percentCompany);
        }
        $partnercompany->setPercent($data['percent']);
        //persiste os dados
        $em = $d->getManager();
        $em->persist($company);
        $em->flush();
        //formata o response
        $data = $company->formatarResponseCompany();

        return $this->json([
            'message' => 'Partner percent updated successfully',
            'data' => $data
        ], 200);

    }

    


}

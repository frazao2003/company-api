<?php
namespace App\Service;

use App\Utils\MascaraCPFeCNPJ;

class FormateResponseDTO{
    private MascaraCPFeCNPJ $mascaraCpfeCnpj;

    public function __construct(MascaraCPFeCNPJ $mascaraCPFeCNPJ){
        $this->mascaraCpfeCnpj = $mascaraCPFeCNPJ;
    }



    public function formatarResponseCompany($company)
    {
        $Partnerscompany = $company->getPartners();
        $cnpjMascarado = $this->mascaraCpfeCnpj->mascaraCNPJ($company->getCnpj());

           $companyData = [
               'id'=>$company->getId(),
               'nomeFantasia'=> $company->getNomeFantasia(),
               'cnpj' => $cnpjMascarado,
               'percent' => $company->getPercent()
           ];
   
   
           $PartnersData = [];
           foreach ($Partnerscompany as $Partnercompany) {
               $cpfMascarado = $this->mascaraCpfeCnpj->mascaraCPF($Partnercompany->getPartner()->getCpf());
               $PartnersData[] = [
                   'partner' => [
                       'nome' =>$Partnercompany->getPartner()->getNome(),
                       'cpf' => $$cpfMascarado,
                   ],
                   'percent' => $Partnercompany->getPercent()
               ];
           }
           $data = [
               "company" => $companyData,
               'partners' => $PartnersData
           ];

           return $data;

    }

    public function formatarPartnerResponse($partner){
        $partnerData  = [
            'id'=>$partner->getId(),
            'nome' =>$partner->getNome(),
            'cpf' =>$partner->getCpf()
        ];
        $partnerCompanies = $partner->partnerCompanyRepository->findAllByPartner($partner);
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
        return $data;
    }

}
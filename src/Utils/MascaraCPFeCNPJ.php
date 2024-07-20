<?php

namespace App\Utils;


class MascaraCPFeCNPJ{
    public function mascaraCNPJ($cnpj){
        $cnpjMascarado = substr($cnpj, 0, 2) . '.***.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-**';
    
        return $cnpjMascarado;
    }

    public function mascaraCPF($cpf){
        $cpfMascarado = substr($cpf, 0, 3) . '.***.' . '***' . '-' . substr($cpf, 9, 2);
    
        return $cpfMascarado;
    }
}
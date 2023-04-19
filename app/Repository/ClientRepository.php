<?php 

namespace App\Repository;

use App\Interface\ClientInterface;
use App\Utils\Utils;
use Illuminate\Support\Facades\Http;
use Throwable;

class ClientRepository implements ClientInterface
{ 
    private $finalResult = [];
    private $cpf;

    public function findCredit(string $cpf){
        try{
            $this->cpf = $cpf;
            
            $financialInstituitions = $this->getInstInformation();
            $this->simuateCreditOffer($financialInstituitions);
            
            return $this->finalResult;

        }catch(Throwable $error){
            throw $error;
        }
    }

    private function getInstInformation(){
        try{
            $response = Http::post(Utils::URL_CREDITO, ['cpf' => $this->cpf]);
            return $response->json();
        }catch(Throwable $error){
            throw $error;
        }
    }

    private function simuateCreditOffer(array $instituitions){
        
        foreach($instituitions['instituicoes'] as $value){
            $this->startCalculate($value['id'], $value['nome'], $value['modalidades']);
        }
    }

    private function startCalculate(int $instituition_id, string $instituitionName, array $modality){
        try{

            $params = [
                'cpf' => $this->cpf,
                'instituicao_id' => $instituition_id,
                'codModalidade' => ''
            ];

            foreach ($modality as $key => $values) {
                $params['codModalidade'] = $values['cod'];
                $response = Http::post(Utils::URL_OFFER, $params);
                $this->calculateOffer($response->json(), $instituitionName, $values['nome']);
            }
            
        }catch(Throwable $error){
            throw $error;
        }
    }

private function calculateOffer(array $offer, string $instituitionName, string $modalityName){
    
    $valueMinToPay = $offer['valorMin'];
    $valueMaxToPay = $offer['valorMax'];

    for($i = 0; $i < $offer['QntParcelaMin']; $i++){
        $valueMinToPay += $valueMinToPay * $offer['jurosMes'];    
    }

    for($i = 0; $i < $offer['QntParcelaMax']; $i++){
        $valueMaxToPay += $valueMaxToPay * $offer['jurosMes'];    
    }

    $resultToPay = $valueMaxToPay;
    $resultAsked = $offer['valorMax'];
    $timesToPay = $offer['QntParcelaMax'];
    
    if($valueMinToPay < $valueMaxToPay){
        $resultToPay = $valueMinToPay;
        $resultAsked = $offer['valorMin'];
        $timesToPay = $offer['QntParcelaMin'];
    }
    
    $this->finalResult[] = [
        'instituicaoFinanceira' => $instituitionName,
        'modalidadeCredito' => $modalityName,
        'valorAPagar' =>  number_format((float)$resultToPay, 2, '.', ''),
        'valorSolicitado' => number_format((float)$resultAsked, 2, '.', ''),
        'taxaJuros' => $offer['jurosMes'],
        'qntParcelas' => $timesToPay
    ];

    $this->finalResult = collect($this->finalResult)->sortBy('valorAPagar')->toArray();
}
    
}
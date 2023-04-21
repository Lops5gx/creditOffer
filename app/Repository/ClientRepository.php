<?php 

namespace App\Repository;

use App\Interface\ClientInterface;
use App\Models\Offers;
use App\Utils\Utils;
use Illuminate\Support\Facades\Http;
use Throwable;

class ClientRepository implements ClientInterface
{ 
    private $finalResult = [];
    private $allValues = [];
    private $cpf;
    private $offerModel;

    public function __construct(Offers $offerModel)
    {
        $this->offerModel = $offerModel;    
    }

    /**
    * Search Credit to Simulate
    * @param String $cpf
    * @return Array
    */
    public function findCredit(string $cpf){
        try{
            $this->cpf = $cpf;
            
            $financialInstituitions = $this->getInstInformation();
            $this->simuateCreditOffer($financialInstituitions);
            dd($this->allValues);
            return $this->finalResult; 

        }catch(Throwable $error){
            throw $error;
        }
    }

    /**
    * Get Instituition Informations
    * @return Array
    */
    private function getInstInformation(){
        try{
            $response = Http::post(Utils::URL_CREDITO, ['cpf' => $this->cpf]);
            return $response->json();
        }catch(Throwable $error){
            throw $error;
        }
    }

    /**
    * Simulate Credit Offer
    * @param Array $instituitions
    */
    private function simuateCreditOffer(array $instituitions){
        
        foreach($instituitions['instituicoes'] as $value){
            $this->startCalculate($value['id'], $value['nome'], $value['modalidades']);
        }

        $this->calculateOffer();
        die;
    }

    /**
    * Start the calculations of each offer
    * @param Int $instituition_id
    * @param String $instituitionName
    * @param Array $modality
    */
    private function startCalculate(int $instituition_id, string $instituitionName, array $modality){
        try{

            $params = [
                'cpf' => $this->cpf,
                'instituicao_id' => $instituition_id,
                'codModalidade' => ''
            ];

            foreach ($modality as $key => $values) {
                $params['codModalidade'] = $values['cod'];
                $response = Http::post(Utils::URL_OFFER, $params)->json();
                $response['nomeInstituicao'] = $instituitionName;
                $response['nomeModalidade'] = $values['nome'];
                $this->allValues[] = $response;
            }
        }catch(Throwable $error){
            throw $error;
        }
    }

    /**
    * Calculate the offer
    * @param Array $offer
    * @param String $instituitionName
    * @param String $modalityName
    */
    private function calculateOffer(){
        
        $result = [];

        foreach ($this->allValues as $key => $value) {
            $valueMinToPay = $value['valorMin'];
            $timeToPayMin = $value['QntParcelaMin'];
            
            $valueMaxToPay = $value['valorMax'];
            $timeToPayMax = $value['QntParcelaMax'];
            
            $tax = $value['jurosMes'];

            //valueMinTimeMin
            $result[0]['valorAPagar'] = number_format((float)($valueMinToPay + (($valueMinToPay * $tax) * $timeToPayMin)), 2, '.', '');
            $result[0]['instituicaoFinanceira'] = $value['nomeInstituicao'];
            $result[0]['modalidadeCredito'] = $value['nomeModalidade'];
            $result[0]['valorSolicitado'] =  $valueMinToPay;
            $result[0]['taxaJuros'] = $tax;
            $result[0]['qntParcelas'] = $timeToPayMin;
            
            //valueMinTimeMax
            $result[]['valorAPagar'] = number_format((float)($valueMinToPay + (($valueMinToPay * $tax) * $timeToPayMax)), 2, '.', '');
            $result[]['instituicaoFinanceira'] = $value['nomeInstituicao'];
            $result[]['modalidadeCredito'] = $value['nomeModalidade'];
            $result[]['valorSolicitado'] =  $valueMinToPay;
            $result[]['taxaJuros'] = $tax;
            $result[]['qntParcelas'] = $timeToPayMax;

            //valueMaxTimeMin
            $result['valorAPagar'] =  number_format((float)($valueMaxToPay + (($valueMinToPay * $tax) * $timeToPayMin)), 2, '.', '');
            $result['instituicaoFinanceira'] = $value['nomeInstituicao'];
            $result['modalidadeCredito'] = $value['nomeModalidade'];
            $result['valorSolicitado'] =  $valueMaxToPay;
            $result['taxaJuros'] = $tax;
            $result['qntParcelas'] = $timeToPayMin;

            //valueMaxTimeMax
            $result['valueMaxTimeMax'][$key]['valorAPagar'] =  number_format((float)($valueMaxToPay + (($valueMinToPay * $tax) * $timeToPayMax)), 2, '.', '');
            $result['valueMaxTimeMax'][$key]['instituicaoFinanceira'] =  $value['nomeInstituicao'];
            $result['valueMaxTimeMax'][$key]['modalidadeCredito'] =  $value['nomeModalidade'];
            $result['valueMaxTimeMax'][$key]['valorSolicitado'] =  $valueMaxToPay;
            $result['valueMaxTimeMax'][$key]['taxaJuros'] = $tax;
            $result['valueMaxTimeMax'][$key]['qntParcelas'] = $timeToPayMax;

        }
        $this->allValues = $result;
        dd($this->allValues);
    }

    private function getTheBest(){
        
        $menorValor = 0;
        
        foreach ($this->allValues as $key => $types) {
            
            dump($types);
            foreach ($types as $key => $value) {
                dd($value, $this->allValues);
            }
        }
    }
}
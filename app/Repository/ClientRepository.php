<?php 

namespace App\Repository;

use App\Interface\ClientInterface;
use App\Models\Offers;
use App\Utils\Utils;
use Illuminate\Support\Facades\Http;
use Throwable;

class ClientRepository implements ClientInterface
{ 
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

            return $this->allValues; 

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

            foreach ($modality as $modalityKey => $values) {
                $params['codModalidade'] = $values['cod'];
                $response = Http::post(Utils::URL_OFFER, $params)->json();
                $response['nomeInstituicao'] = $instituitionName;
                $response['nomeModalidade'] = $values['nome'];
                   
                $objeto = new \stdClass();
                $objeto->QntParcelaMin = $response['QntParcelaMin'];
                $objeto->QntParcelaMax = $response['QntParcelaMax'];
                $objeto->valorMin = $response['valorMin'];
                $objeto->valorMax = $response['valorMax'];
                $objeto->jurosMes = $response['jurosMes'];
                $objeto->nomeInstituicao = $response['nomeInstituicao'];
                $objeto->nomeModalidade = $response['nomeModalidade'];
                $this->allValues[] = $objeto;
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
    private function calculateOffer()
    {
        $result = [];

        foreach ($this->allValues as $key => $value) {
            
            $valueMinToPay = $value->valorMin;
            $timeToPayMin = $value->QntParcelaMin;
            $valueMaxToPay = $value->valorMax;
            $timeToPayMax = $value->QntParcelaMax;
            
            $tax = $value->jurosMes;

            //valueMinTimeMin
            $valueMinTimeMin = new \stdClass();
            $valueMinTimeMin->valorAPagar = number_format((float)($valueMinToPay + (($valueMinToPay * $tax) * $timeToPayMin)), 2, '.', '');
            $valueMinTimeMin->instituicaoFinanceira =  $value->nomeInstituicao;
            $valueMinTimeMin->modalidadeCredito = $value->nomeModalidade;
            $valueMinTimeMin->valorSolicitado =  $valueMinToPay;
            $valueMinTimeMin->taxaJuros = $tax;
            $valueMinTimeMin->qntParcelas = $timeToPayMin;
            $result[] = $valueMinTimeMin;
            

            $valueMinTimeMax = new \stdClass();
            $valueMinTimeMax->valorAPagar = number_format((float)($valueMinToPay + (($valueMinToPay * $tax) * $timeToPayMax)), 2, '.', '');
            $valueMinTimeMax->instituicaoFinanceira = $value->nomeInstituicao;
            $valueMinTimeMax->modalidadeCredito = $value->nomeModalidade;
            $valueMinTimeMax->valorSolicitado =  $valueMinToPay;
            $valueMinTimeMax->taxaJuros = $tax;
            $valueMinTimeMax->qntParcelas = $timeToPayMax;
            $result[] = $valueMinTimeMax;


            $valueMaxTimeMin = new \stdClass();
            $valueMaxTimeMin->valorAPagar = number_format((float)($valueMaxToPay + (($valueMinToPay * $tax) * $timeToPayMin)), 2, '.', '');
            $valueMaxTimeMin->instituicaoFinanceira = $value->nomeInstituicao;
            $valueMaxTimeMin->modalidadeCredito = $value->nomeModalidade;
            $valueMaxTimeMin->valorSolicitado =  $valueMaxToPay;
            $valueMaxTimeMin->taxaJuros = $tax;
            $valueMaxTimeMin->qntParcelas = $timeToPayMin;
            $result[] = $valueMaxTimeMin;


            $valueMaxTimeMax = new \stdClass();
            $valueMaxTimeMax->valorAPagar = number_format((float)($valueMaxToPay + (($valueMinToPay * $tax) * $timeToPayMax)), 2, '.', '');
            $valueMaxTimeMax->instituicaoFinanceira = $value->nomeInstituicao;
            $valueMaxTimeMax->modalidadeCredito = $value->nomeModalidade;
            $valueMaxTimeMax->valorSolicitado = $valueMaxToPay;
            $valueMaxTimeMax->taxaJuros = $tax;
            $valueMaxTimeMax->qntParcelas = $timeToPayMax;
            $result[] = $valueMaxTimeMax;

        }
        $this->allValues = $result;
        $this->getTheBest();
        $this->saveOffers();
    }

    private function getTheBest(){
        
        usort($this->allValues, function ($a, $b) {
            return $a->valorAPagar - $b->valorAPagar;
        });
        
        $this->allValues = array_slice($this->allValues, 0, 3); 
    }

    private function saveOffers(){
        try{
            foreach($this->allValues as $value){
                $this->offerModel->create(
                    $value->instituicaoFinanceira,
                    $value->modalidadeCredito,
                    $value->valorAPagar,
                    $value->valorSolicitado,
                    $value->taxaJuros,
                    $value->qntParcelas);
            }
        }catch(\Throwable $error){
            throw $error;
        }
    }
}
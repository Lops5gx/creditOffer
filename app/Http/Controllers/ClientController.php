<?php

namespace App\Http\Controllers;

use App\Interface\ClientInterface;
use App\Http\Request\Client\Index;
use App\Models\Client;
use App\Models\Offers;
use App\Utils\Utils;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    private $client;
    private $model;

    public function __construct(ClientInterface $client, Client $modelClient)
    {
        $this->client = $client;
        $this->model = $modelClient;
    }

    /**
    * Search CPF to Simulate
    * @param String $cpf
    * @return Array
    */
    public function search(Index $request){

        try{
            $cpf = $this->model->search($request->cpf);
            $data = 'CPF não encontrado!';
            
            if($cpf){
                $data = $this->client->findCredit($request->cpf);
            }
            
            return Utils::defaultReturn($data);
        }catch(\Throwable $error){
            return Utils::defaultReturn($error->getMessage());
        }
    }
}

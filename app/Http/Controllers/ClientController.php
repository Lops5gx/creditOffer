<?php

namespace App\Http\Controllers;

use App\Interface\ClientInterface;
use App\Http\Request\Client\Index;
use App\Utils\Utils;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }


    public function search(Index $request){

        try{
            $data = $this->client->findCredit($request->cpf);
            
            return Utils::defaultReturn($data);
        }catch(\Throwable $error){
            return Utils::defaultReturn($error->getMessage());
        }
    }
}

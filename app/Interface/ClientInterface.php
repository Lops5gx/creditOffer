<?php

namespace App\Interface;


interface ClientInterface
{
    /**
    * Search Credit to Simulate
    * @param String $cpf
    * @return Array
    */
    public function findCredit(string $cpf);
}

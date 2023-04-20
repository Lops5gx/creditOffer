<?php 

namespace App\Repository;

use App\Interface\ReportInterface;
use App\Models\Offers;

Class ReportRepository implements ReportInterface{

    private $offerModel;

    public function __construct(Offers $offerModel)
    {
        $this->offerModel = $offerModel;    
    }

    /**
    * get all credit simulated
    * @return Array
    */
    public function getOffers(){
        try{
            $allOffers = $this->offerModel::get();
            return $allOffers;
        }catch(\Throwable $error){
            throw $error;
        }
    }
}
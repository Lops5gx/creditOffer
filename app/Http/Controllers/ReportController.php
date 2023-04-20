<?php

namespace App\Http\Controllers;

use App\Interface\ReportInterface;
use App\Utils\Utils;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private $report;

    public function __construct(ReportInterface $report)
    {
        $this->report = $report;
    }
    
    /**
    * Search CPF to Simulate
    * @return Array
    */
    public function getReport(Request $request){
        try{

            $data = $this->report->getOffers();

            return Utils::defaultReturn($data);
        }catch(\Throwable $error){
            return Utils::defaultReturn($error->getMessage());
        }
    }
}

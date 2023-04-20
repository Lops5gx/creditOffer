<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offers extends Model
{
    use HasFactory;
     /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'offers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'instituicaoFinanceira',
        'modalidadeCredito',
        'valorAPagar',
        'valorSolicitado',
        'taxaJuros',
        'qntParcelas'
    ];

    /**
    * Search CPF into database
    * @param String $instituicaoFinanceira
    * @param String $modalidadeCredito
    * @param Float $valorAPagar
    * @param Float $valorSolicitado
    * @param Float $taxaJuros
    * @param Float $qntParcelas
    * @return Bool
    */
    public function create(String $instituicaoFinanceira, String $modalidadeCredito, float $valorAPagar, float $valorSolicitado, float $taxaJuros, int $qntParcelas){
        try{
            $values = [$instituicaoFinanceira, $modalidadeCredito, $valorAPagar, $valorSolicitado, $taxaJuros, $qntParcelas];
            $keyAndValues = array_combine($this->fillable, $values);

            $result = Offers::insert($keyAndValues);
            
            return $result;
        }catch(\Throwable $error){
            throw $error;
        }
    }
}

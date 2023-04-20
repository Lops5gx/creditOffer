<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client';
    protected $primaryKey = 'id';

    protected $fillable = ['cpf'];

    /**
    * Search CPF into database
    * @param String $cpf
    * @return Array
    */
    public function search(String $cpf){
        try{
            $selected = Client::where('cpf', $cpf)->get();
            return $selected;
        }catch(\Throwable $error){
            throw $error;
        }
    }
}

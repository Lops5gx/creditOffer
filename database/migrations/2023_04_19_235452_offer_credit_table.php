<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('instituicaoFinanceira', 100);
            $table->string('modalidadeCredito', 100);
            $table->float('valorAPagar', 10, 2);
            $table->float('valorSolicitado', 10, 2);
            $table->float('taxaJuros', 10, 2);
            $table->integer('qntParcelas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    
    }
};

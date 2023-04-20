<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cpfs = [
            ['cpf' => '11111111111'],
            ['cpf' => '12312312312'],
            ['cpf' => '22222222222']
        ];

        DB::table('client')->insert($cpfs);
    }
}

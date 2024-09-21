<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PersonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('people')->insert([
            'uuid' => Str::uuid(),
            'name' => "William Samoei Ruto",
            'email' => 'william.ruto@example.go.ke',
            'about' =>  "5th president of Kenya",
            'gender' => "male",
            'created_at' => now()
        ]);

        DB::table('people')->insert([
            'uuid' => Str::uuid(),
            'name' => "Uhuru Kenyatta",
            'email' => 'uhuru.kenyatta@kenya.go.ke',
            'about' =>  "4th president of Kenya",
            'gender' => "male",
            'created_at' => now()
        ]);

        DB::table('people')->insert([
            'uuid' => Str::uuid(),
            'name' => "Mwai Kibaki",
            'email' => 'mwai.kibaki@kenya.go.ke',
            'about' =>  "3th president of Kenya",
            'gender' => "male",
            'created_at' => now()
        ]);

        DB::table('people')->insert([
            'uuid' => Str::uuid(),
            'name' => "Daniel Toroitich Arap Moi",
            'email' => 'daniel.moi@kenya.go.ke',
            'about' =>  "2nd president of Kenya",
            'gender' => "male",
            'created_at' => now()
        ]);

        DB::table('people')->insert([
            'uuid' => Str::uuid(),
            'name' => "Jomo Kenyatta",
            'email' => 'jomo.kenyatta@kenya.go.ke',
            'about' =>  "1st president of Kenya",
            'gender' => "male",
            'created_at' => now()
        ]);
    }
}

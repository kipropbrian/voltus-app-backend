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
            'uuid' => Str::random(34),
            'name' => "William Samoei Ruto",
            'about' =>  "5th president of Kenya",
            'gender' => "M",
            'created_at' => now()
        ]);
        
        DB::table('people')->insert([
            'uuid' => Str::random(34),
            'name' => "Uhuru Kenyatta",
            'about' =>  "4th president of Kenya",
            'gender' => "M",
            'created_at' => now()
        ]);

        DB::table('people')->insert([
            'uuid' => Str::random(34),
            'name' => "Mwai Kibaki",
            'about' =>  "3th president of Kenya",
            'gender' => "M",
            'created_at' => now()
        ]);

        DB::table('people')->insert([
            'uuid' => Str::random(34),
            'name' => "Daniel Toroitich Arap Moi",
            'about' =>  "2nd president of Kenya",
            'gender' => "M",
            'created_at' => now()
        ]);

        DB::table('people')->insert([
            'uuid' => Str::random(34),
            'name' => "Jomo Kenyatta",
            'about' =>  "1st president of Kenya",
            'gender' => "M",
            'created_at' => now()
        ]);
    }
}

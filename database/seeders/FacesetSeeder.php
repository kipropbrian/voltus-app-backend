<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FacesetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('facesets')->insert([
            'uuid' => Str::uuid(),
            'display_name' => "voltus_test",
            'outer_id' => "16cd25de-f82c-4fa9-8b45-6a091bede2e6",
            'faceset_token' => "be300fcc615f195cb6a772dda374797e",
            'status' => "active",
            'created_at' => now()
        ]);
    }
}

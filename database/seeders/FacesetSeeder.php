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
            'display_name' => "test_voltus",
            'outer_id' => "084fc771-5469-40b2-894e-c92c469b0884",
            'faceset_token' => "5684e0715c69b7a8ee74463841839d6c",
            'status' => "active",
            'created_at' => now()
        ]);
    }
}

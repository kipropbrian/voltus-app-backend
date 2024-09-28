<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('images')->insert([
            'uuid' => Str::uuid(),
            'image_url' => 'http://res.cloudinary.com/voltus/image/upload/v1667340994/voltus/xzmksyvrbmbtyldnc00l.png',
            'image_url_secure' => 'https://res.cloudinary.com/voltus/image/upload/v1667340994/voltus/xzmksyvrbmbtyldnc00l.png',
            'size' => '285.41 KB',
            'filetype' => 'image',
            'originalFilename' => 'phpKv4CTA',
            'publicId' => 'voltus/xzmksyvrbmbtyldnc00l',
            'extension' => 'png',
            'width' => '507',
            'height' => '460',
            'timeUploaded' => '2022-11-01T22:16:34Z',
            'person_id' => 1,
            'md5' => md5(random_int(1, 10000)),
            'created_at' => now(),
        ]);

        DB::table('images')->insert([
            'uuid' => Str::uuid(),
            'image_url'  => 'http://res.cloudinary.com/voltus/image/upload/v1667342113/voltus/c2klbud3okzxyljgtdw3.jpg',
            'image_url_secure'  => 'https://res.cloudinary.com/voltus/image/upload/v1667342113/voltus/c2klbud3okzxyljgtdw3.jpg',
            'size'  => '208.70 KB',
            'filetype'  => 'image',
            'originalFilename'  => 'phpZ2p0uW',
            'publicId'  => 'voltus/c2klbud3okzxyljgtdw3',
            'extension'  => 'jpg',
            'width'  => '1600',
            'height'  => '1600',
            'timeUploaded'  => '2022-11-01T22:35:13Z',
            'person_id'  => 1,
            'md5' => md5(random_int(1, 10000)),
            'created_at'  => now(),
        ]);

        DB::table('images')->insert([
            'uuid' => Str::uuid(),
            'image_url' => 'http://res.cloudinary.com/voltus/image/upload/v1667416674/voltus/c1lpfklwpk5ykqsi9ta5.jpg',
            'image_url_secure' => 'https://res.cloudinary.com/voltus/image/upload/v1667416674/voltus/c1lpfklwpk5ykqsi9ta5.jpg',
            'size' => '727.71 KB',
            'filetype' => 'image',
            'originalFilename' => 'phpginqmw',
            'publicId' => 'voltus/c1lpfklwpk5ykqsi9ta5',
            'extension' => 'jpg',
            'width' => '724',
            'height' => '926',
            'timeUploaded' => '2022-11-02T19:17:54Z',
            'person_id' => 2,
            'md5' => md5(random_int(1, 10000)),
            'created_at' => now(),
        ]);

        DB::table('images')->insert([
            'uuid' => Str::uuid(),
            'image_url' => 'http://res.cloudinary.com/voltus/image/upload/v1667416961/voltus/v5fvetqj43kqsfvg5nup.jpg',
            'image_url_secure' => 'https://res.cloudinary.com/voltus/image/upload/v1667416961/voltus/v5fvetqj43kqsfvg5nup.jpg',
            'size' => '54.96 KB',
            'filetype' => 'image',
            'originalFilename' => 'php16zzHg',
            'publicId' => 'voltus/v5fvetqj43kqsfvg5nup',
            'extension' => 'jpg',
            'width' => '800',
            'height' => '533',
            'timeUploaded' => '2022-11-02T19:22:41Z',
            'person_id' => 3,
            'md5' => md5(random_int(1, 10000)),
            'created_at' => now()
        ]);

        DB::table('images')->insert([
            'uuid' => Str::uuid(),
            'image_url' => 'http://res.cloudinary.com/voltus/image/upload/v1667417282/voltus/hlo38osoktovx7a0no7v.jpg',
            'image_url_secure' => 'https://res.cloudinary.com/voltus/image/upload/v1667417282/voltus/hlo38osoktovx7a0no7v.jpg',
            'size' => '111.99 KB',
            'filetype' => 'image',
            'originalFilename' => 'phpKX6xZO',
            'publicId' => 'voltus/hlo38osoktovx7a0no7v',
            'extension' => 'jpg',
            'width' => '976',
            'height' => '549',
            'timeUploaded' => '2022-11-02T19:28:02Z',
            'person_id' => 4,
            'md5' => md5(random_int(1, 10000)),
            'created_at' => now(),
        ]);

        DB::table('images')->insert([
            'uuid' => Str::uuid(),
            'image_url' => 'http://res.cloudinary.com/voltus/image/upload/v1667417600/voltus/xp8vnhvyo1qcbpdbmjjo.jpg',
            'image_url_secure' => 'https://res.cloudinary.com/voltus/image/upload/v1667417600/voltus/xp8vnhvyo1qcbpdbmjjo.jpg',
            'size' => '515.28 KB',
            'filetype' => 'image',
            'originalFilename' => 'phpPomd1N',
            'publicId' => 'voltus/xp8vnhvyo1qcbpdbmjjo',
            'extension' => 'jpg',
            'width' => '1043',
            'height' => '1600',
            'timeUploaded' => '2022-11-02T19:33:20Z',
            'md5' => md5(random_int(1, 10000)),
            'created_at' => now(),
        ]);
    }
}

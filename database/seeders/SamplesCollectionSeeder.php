<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SamplesCollectionSeeder extends Seeder
{
    public function run()
    {
        // delete existing collection
        DB::table('samples')->delete();

        // get data
        $json = File::get('database/seeds/samples.json');
        $data = json_decode($json, true);

        foreach ($data as $t) {
            // add to collection
            DB::collection('samples')->insert($t);
        }
    }
}

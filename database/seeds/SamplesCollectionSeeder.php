<?php

use Illuminate\Database\Seeder;

class SamplesCollectionSeeder extends Seeder
{
    public function run()
    {
        // delete existing collection
        DB::table('samples')->delete();

        // create/seed collection
        $json = File::get('database/seeds/samples.json');
        $data = json_decode($json);
        foreach ($data as $obj) {
            DB::collection('samples')->insert($data);
        }
    }
}

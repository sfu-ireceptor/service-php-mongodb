<?php

use Illuminate\Database\Seeder;

class SequencesCollectionSeeder extends Seeder
{
    public function run()
    {
        // delete existing collection
        DB::table('sequences')->delete();

        // get data
        $json = File::get('database/seeds/sequences.json');
        $data = json_decode($json, true);

        foreach ($data as $t) {
	        // unset "_id" field
            unset($t['_id']);
	
            // add to collection
	        DB::collection('sequences')->insert($t);
        }
    }
}

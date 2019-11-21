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
        $data = json_decode($json);

        // unset "_id" field
        foreach ($data as $i => $obj) {
        	unset($obj->_id);
        }

        // populate collection
        DB::collection('sequences')->insert($data);

    }
}

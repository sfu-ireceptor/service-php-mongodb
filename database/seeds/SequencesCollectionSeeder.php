<?php

use Illuminate\Database\Seeder;
use MongoDB\BSON\ObjectID;

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
            $id = $t['_id']['$oid'];
            $t['_id'] = new ObjectID($id);

            // add to collection
            DB::collection('sequences')->insert($t);
        }
    }
}

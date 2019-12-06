<?php

namespace Tests\Feature;

use SequencesCollectionSeeder;
use Tests\TestCase;

class RearrangementWithIdTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->seed(SequencesCollectionSeeder::class);
    }

    /** @testx */
    public function check_valid_JSON_response()
    {
        $response = $this->getJson('/airr/v1/rearrangement/5ddd883fa9832008fe088702');
        $response->assertStatus(200);

        $response->assertHeader('content-type', 'application/json');

        $response->assertJson([]);
    }

    /** @testx */
    public function check_correct_JSON_response()
    {
        $response = $this->getJson('/airr/v1/rearrangement/5ddd883fa9832008fe088702');

        // has info and rearrangement objects
        $response->assertJson(['Info' => []]);
        $response->assertJson(['Rearrangement' => []]);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(1, $t->Rearrangement);

        $rearrangement_id = data_get($t, 'Rearrangement.0.rearrangement_id');
        $this->assertEquals('5ddd883fa9832008fe088702', $rearrangement_id);
    }
}

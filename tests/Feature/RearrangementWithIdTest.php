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

    /** @test */
    public function check_valid_JSON_response()
    {
        $response = $this->getJson('/airr/v1/rearrangement/5d2cd9eb9a6c030b30832c00');
        $response->assertStatus(200);

        $response->assertHeader('content-type', 'application/json');

        $response->assertJson([]);
    }

    /** @test */
    public function check_correct_JSON_response()
    {
        $response = $this->getJson('/airr/v1/rearrangement/5d2cd9eb9a6c030b30832c00');

        // has info and rearrangement objects
        $response->assertJson(['Info' => []]);
        $response->assertJson(['Rearrangement' => []]);

        $json = $response->content();
        $t = json_decode($json);

        $rearrangement_id = data_get($t, 'Rearrangement.rearrangement_id');
        $this->assertEquals('5d2cd9eb9a6c030b30832c00', $rearrangement_id);
    }
}

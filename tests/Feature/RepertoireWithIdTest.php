<?php

namespace Tests\Feature;

use SamplesCollectionSeeder;
use Tests\TestCase;

class RepertoireWithIdTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->seed(SamplesCollectionSeeder::class);
    }

    /** @test */
    public function check_valid_JSON_response()
    {
        $response = $this->getJson('/airr/v1/repertoire/9');
        $response->assertStatus(200);

        $response->assertHeader('content-type', 'application/json');

        $response->assertJson([]);
    }

    /** @test */
    public function check_correct_JSON_response()
    {
        $response = $this->getJson('/airr/v1/repertoire/9');

        // has info and repertoire objects
        $response->assertJson(['Info' => []]);
        $response->assertJson(['Repertoire' => []]);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(1, $t->Repertoire);

        $repertoire_id = data_get($t, 'Repertoire.0.repertoire_id');
        $this->assertEquals('9', $repertoire_id);
    }
}

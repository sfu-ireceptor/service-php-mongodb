<?php

namespace Tests\Feature;

use SequencesCollectionSeeder;
use Tests\TestCase;

class RearrangementTest extends TestCase
{
    public function postJsonString($uri, $content)
    {
        $headers = [
            'CONTENT_LENGTH' => mb_strlen($content, '8bit'),
            'CONTENT_TYPE' => 'application/json',
            'Accept' => 'application/json',
        ];

        $response = $this->call('POST', $uri, [], [], [], $this->transformHeadersToServerVars($headers), $content);

        return $response;
    }

    public function setUp()
    {
        parent::setUp();
        $this->seed(SequencesCollectionSeeder::class);
    }

    /** @test */
    public function check_valid_JSON_response()
    {
        $response = $this->postJson('/airr/v1/rearrangement');
        $response->assertStatus(200);

        $json = $response->streamedContent();
        $t = json_decode($json);

        if (is_null($t) || $t === false) {
            $this->fail('Invalid JSON');
        }
    }

    /** @test */
    public function check_correct_JSON_response()
    {
        $response = $this->postJson('/airr/v1/rearrangement');

        $json = $response->streamedContent();
        $t = json_decode($json);

        if (! is_object(data_get($t, 'Info'))) {
            $this->fail('No Info object');
        }

        if (! is_object(data_get($t, 'Info'))) {
            $this->fail('No Rearrangement object');
        }

        $this->assertCount(20, $t->Rearrangement);
    }

    /** @test */
    public function repertoire_id()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "in",
    "content": {
      "field": "repertoire_id",
      "value": [
        "8"
      ]
    }
  }
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $response->assertStatus(200);

        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(10, $t->Rearrangement);

        $first_repertoire_id = data_get($t, 'Rearrangement.0.repertoire_id');
        $this->assertEquals($first_repertoire_id, '8');
    }
}

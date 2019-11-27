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

    // TODO not sure why there's a warning about the headers??
    // /** @test */
    // public function check_valid_JSON_response()
    // {
    //     $response = $this->postJson('/airr/v1/rearrangement');
    //     // dd($response);
    //     $response->assertStatus(200);

    //     // $response->assertJson([]);
    // }

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

        // $this->setOutputCallback(function() {});

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        // $response->assertStatus(200);

        $json = $response->content();
        $t = json_decode($json);

        // dd($t);

        // has exactly 1 sample
        $this->assertCount(1, $t->Rearrangement);

        // // female sample
        // $sex = data_get($t, 'Repertoire.0.subject.sex');
        // $this->assertEquals($sex, 'Female');
    }
}

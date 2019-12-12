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

        $response->assertHeader('content-type', 'application/json');

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

        if (! is_array(data_get($t, 'Rearrangement'))) {
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
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(10, $t->Rearrangement);

        $first_repertoire_id = data_get($t, 'Rearrangement.0.repertoire_id');
        $this->assertIsString($first_repertoire_id);
        $this->assertEquals($first_repertoire_id, '8');
    }

    /** @test */
    public function range()
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
  },
  "from": 0,
  "size": 4
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(4, $t->Rearrangement);

        $first_repertoire_id = data_get($t, 'Rearrangement.0.repertoire_id');
        $this->assertEquals($first_repertoire_id, '8');
    }

    /** @test */
    public function range_with_fields()
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
  },
  "from": 0,
  "size": 3,
  "fields": [
    "v_call",
    "d_call",
    "junction_aa"
  ]
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(3, $t->Rearrangement);

        $first_rearrangement = data_get($t, 'Rearrangement.0');
        $this->assertObjectHasAttribute('v_call', $first_rearrangement);
        $this->assertObjectHasAttribute('d_call', $first_rearrangement);
        $this->assertObjectHasAttribute('junction_aa', $first_rearrangement);

        if (isset($first_rearrangement->j_call)) {
            $this->fail('Unexpected field: j_call');
        }
    }

    /** @test */
    // IR-1552 - Productive filter on gateway not working
    public function productive_filter_true()
    {
        $s = <<<'EOT'
{
    "filters": {
        "op": "and",
        "content": [
            {
                "op": "=",
                "content": {
                    "field": "productive",
                    "value": true
                }
            },
            {
                "op": "in",
                "content": {
                    "field": "repertoire_id",
                    "value": [
                        "8"
                    ]
                }
            }
        ]
    }
}
EOT;
        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(10, $t->Rearrangement);

        $first_repertoire_id = data_get($t, 'Rearrangement.0.repertoire_id');
        $this->assertEquals($first_repertoire_id, '8');

        $productive = data_get($t, 'Rearrangement.0.productive');
        $this->assertEquals($productive, true);
    }

    /** @test */
    // IR-1552 - Productive filter on gateway not working
    public function productive_filter_false()
    {
        $s = <<<'EOT'
{
    "filters": {
        "op": "and",
        "content": [
            {
                "op": "=",
                "content": {
                    "field": "productive",
                    "value": false
                }
            },
            {
                "op": "in",
                "content": {
                    "field": "repertoire_id",
                    "value": [
                        "8"
                    ]
                }
            }
        ]
    }
}
EOT;
        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(0, $t->Rearrangement);
    }

    /** @test */
    // IR-1552 - Productive filter on gateway not working
    public function productive_filter_true_with_range()
    {
        $s = <<<'EOT'
{
    "filters": {
        "op": "and",
        "content": [
            {
                "op": "=",
                "content": {
                    "field": "productive",
                    "value": true
                }
            },
            {
                "op": "in",
                "content": {
                    "field": "repertoire_id",
                    "value": [
                        "8"
                    ]
                }
            }
        ]
    },
    "from": 0,
    "size": 5
}
EOT;
        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(5, $t->Rearrangement);

        $first_repertoire_id = data_get($t, 'Rearrangement.0.repertoire_id');
        $this->assertEquals($first_repertoire_id, '8');

        $productive = data_get($t, 'Rearrangement.0.productive');
        $this->assertEquals($productive, true);
    }

    /** @test */
    public function facet()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "in",
    "content": {
      "field": "repertoire_id",
      "value": [
        "9"
      ]
    }
  },
  "facets": "repertoire_id"
}
EOT;
        $response = $this->postJsonString('/airr/v1/rearrangement', $s);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');

        $json = $response->streamedContent();
        $t = json_decode($json);

        if (is_null($t) || $t === false) {
            $this->fail('Invalid JSON');
        }

        if (! is_object(data_get($t, 'Info'))) {
            $this->fail('No Info object');
        }

        if (! is_array(data_get($t, 'Facet'))) {
            $this->fail('No Facet object');
        }

        $this->assertCount(1, $t->Facet);

        $first_facet = data_get($t, 'Facet.0');
        $this->assertEquals($first_facet->repertoire_id, 9);
        $this->assertIsInt($first_facet->count);
    }

    /** @test */
    public function facet_with_two_repertoire_ids()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "in",
    "content": {
      "field": "repertoire_id",
      "value": [
        "8",
        "9"
      ]
    }
  },
  "facets": "repertoire_id"
}
EOT;
        $response = $this->postJsonString('/airr/v1/rearrangement', $s);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');

        $json = $response->streamedContent();
        $t = json_decode($json);

        if (is_null($t) || $t === false) {
            $this->fail('Invalid JSON');
        }

        if (! is_object(data_get($t, 'Info'))) {
            $this->fail('No Info object');
        }

        if (! is_array(data_get($t, 'Facet'))) {
            $this->fail('No Facet object');
        }

        $this->assertCount(2, $t->Facet);

        $first_facet = data_get($t, 'Facet.0');
        $this->assertEquals($first_facet->repertoire_id, 8);
        $this->assertIsInt($first_facet->count);
    }

    // TODO: "facets" parameter not picked up?!?
    /** @test */
    // IR-1552 - Productive filter on gateway not working
    public function productive_filter_true_with_facet()
    {
        $s = <<<'EOT'
{
"filters": {
    "op": "and",
    "content": [
        {
            "op": "=",
            "content": {
                "field": "productive",
                "value": true
            }
        },
        {
            "op": "in",
            "content": {
                "field": "repertoire_id",
                "value": [
                    "8"
                ]
            }
        }
    ]
},
"facets": "repertoire_id"
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(1, $t->Facet);

        $first_repertoire_id = data_get($t, 'Facet.0.repertoire_id');
        $this->assertEquals($first_repertoire_id, '8');

        $first_repertoire_count = data_get($t, 'Facet.0.count');
        $this->assertEquals($first_repertoire_count, 10);
    }

    /** @test */
    // IR-1544 - no productive and rev_comp fields displayed on gateway
    public function correct_rev_comp_value()
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
        $json = $response->streamedContent();
        $t = json_decode($json);

        $first_rearrangement = data_get($t, 'Rearrangement.0');
        $this->assertEquals($first_rearrangement->rev_comp, true);
    }

    /** @test */
    // IR-1485 - MongoDB error for facet query
    public function d_call_contains()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "and",
    "content": [
      {
        "op": "contains",
        "content": {
          "field": "d_call",
          "value": "IGHD4-11*01"
        }
      },
      {
        "op": "in",
        "content": {
          "field": "repertoire_id",
          "value": [
            "8"
          ]
        }
      }
    ]
  },
  "facets": "repertoire_id"
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(1, $t->Facet);
        $first_facet = data_get($t, 'Facet.0');
        $this->assertEquals($first_facet->repertoire_id, '8');
        $this->assertEquals($first_facet->count, 10);
    }

    /** @test */
    public function d_call()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "d_call",
      "value": "IGHD4-11*01"
    }
  },
  "fields": [
    "repertoire_id",
    "d_call"
  ]
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(10, $t->Rearrangement);

        $first_rearrangement = data_get($t, 'Rearrangement.0');
        $this->assertContains('IGHD4-11*01', $first_rearrangement->d_call);
        $this->assertContains($first_rearrangement->repertoire_id, ['8', '9']);
    }

    /** @test */
    public function j_call()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "j_call",
      "value": "IGHJ6*02"
    }
  },
  "fields": [
    "repertoire_id",
    "j_call"
  ]
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(1, $t->Rearrangement);

        $first_rearrangement = data_get($t, 'Rearrangement.0');
        $this->assertContains('IGHJ6*02', $first_rearrangement->j_call);
        $this->assertContains($first_rearrangement->repertoire_id, ['9']);
    }

    /** @test */
    public function v_call()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "v_call",
      "value": "IGHV4-39*05"
    }
  },
  "fields": [
    "repertoire_id",
    "v_call"
  ]
}
EOT;

        $response = $this->postJsonString('/airr/v1/rearrangement', $s);
        $json = $response->streamedContent();
        $t = json_decode($json);

        $this->assertCount(1, $t->Rearrangement);

        $first_rearrangement = data_get($t, 'Rearrangement.0');
        $this->assertContains('IGHV4-39*05', $first_rearrangement->v_call);
        $this->assertContains($first_rearrangement->repertoire_id, ['8']);
    }
}

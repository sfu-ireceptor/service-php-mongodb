<?php

namespace Tests\Feature;

use SamplesCollectionSeeder;
use Tests\TestCase;

class RepertoireTest extends TestCase
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
        $this->seed(SamplesCollectionSeeder::class);
    }

    /** @test */
    public function check_valid_JSON_response()
    {
        $response = $this->postJson('/airr/v1/repertoire');
        $response->assertStatus(200);

        $response->assertHeader('content-type', 'application/json');

        $response->assertJson([]);
    }

    /** @test */
    public function check_correct_JSON_response()
    {
        $response = $this->postJson('/airr/v1/repertoire');

        // has info and repertoire objects
        $response->assertJson(['Info' => []]);
        $response->assertJson(['Repertoire' => []]);

        $json = $response->content();
        $t = json_decode($json);

        // has exactly 2 samples
        $this->assertCount(2, $t->Repertoire);
    }

    /** @test */
    public function query_with_invalid_json()
    {
        // extra closing brace at the end
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "subject.sex",
      "value": "Female"
    }
  }
}}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        // HTTP status
        $response->assertStatus(400);

        $json = $response->content();
        $t = json_decode($json);

        // error message
        $error_message = data_get($t, 'message');
        $this->assertEquals($error_message, 'Unable to parse JSON parameters:Syntax error');
    }

    /** @test */
    public function query_with_invalid_operator()
    {
        // extra closing brace at the end
        $s = <<<'EOT'
{
  "filters": {
    "op": "bogus",
    "content": {
      "field": "subject.sex",
      "value": "Female"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        // HTTP status
        $response->assertStatus(400);

        $json = $response->content();
        $t = json_decode($json);

        // error message
        $error_message = data_get($t, 'message');
        $this->assertEquals($error_message, 'Unable to parse the filter.');
    }

    /** @test */
    public function query_with_unknown_filter()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "subject.magic",
      "value": "low"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        // HTTP status
        $response->assertStatus(400);

        $json = $response->content();
        $t = json_decode($json);

        // error message
        $error_message = data_get($t, 'message');
        $this->assertEquals($error_message, 'Unable to parse the filter.');
    }

    /** @test */
    public function sex_filter_female()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "subject.sex",
      "value": "Female"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        // has exactly 1 sample
        $this->assertCount(1, $t->Repertoire);

        // female sample
        $sex = data_get($t, 'Repertoire.0.subject.sex');
        $this->assertEquals($sex, 'Female');
    }

    /** @test */
    public function and_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "and",
    "content": [
      {
        "op": ">=",
        "content": {
          "field": "subject.age_min",
          "value": 15
        }
      },
      {
        "op": "=",
        "content": {
          "field": "subject.sex",
          "value": "Female"
        }
      }
    ]
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(1, $t->Repertoire);

        $sex = data_get($t, 'Repertoire.0.subject.sex');
        $this->assertEquals($sex, 'Female');

        $age_min = data_get($t, 'Repertoire.0.subject.age_min');
        $this->assertGreaterThanOrEqual(15, $age_min);
    }

    /** @test */
    public function or_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "or",
    "content": [
      {
        "op": ">=",
        "content": {
          "field": "subject.age_min",
          "value": 60
        }
      },
      {
        "op": "=",
        "content": {
          "field": "subject.sex",
          "value": "Female"
        }
      }
    ]
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(1, $t->Repertoire);

        $sex = data_get($t, 'Repertoire.0.subject.sex');
        $this->assertEquals($sex, 'Female');

        $age_min = data_get($t, 'Repertoire.0.subject.age_min');
        $this->assertLessThan(60, $age_min);
    }

    /** @test */
    public function and_or_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "and",
    "content": [
      {
        "op": "or",
        "content": [
          {
            "op": "=",
            "content": {
              "field": "sample.total_reads_passing_qc_filter",
              "value": 20617
            }
          },
          {
            "op": "=",
            "content": {
              "field": "sample.total_reads_passing_qc_filter",
              "value": 3
            }
          }
        ]
      },
      {
        "op": "=",
        "content": {
          "field": "sample.pcr_target.pcr_target_locus",
          "value": "CDR3"
        }
      }
    ]
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(1, $t->Repertoire);

        $total_reads_passing_qc_filter = data_get($t, 'Repertoire.0.sample.0.total_reads_passing_qc_filter');
        $this->assertEquals($total_reads_passing_qc_filter, 20617);

        $pcr_target_locus = data_get($t, 'Repertoire.0.sample.0.pcr_target.0.pcr_target_locus');
        $this->assertEquals($pcr_target_locus, 'CDR3');
    }

    /** @test */
    public function boolean_equals_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "sample.single_cell",
      "value": true
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(0, $t->Repertoire);
    }

    /** @test */
    public function boolean_not_equals_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "!=",
    "content": {
      "field": "sample.single_cell",
      "value": true
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(2, $t->Repertoire);
    }

    /** @test */
    public function contains_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "contains",
    "content": {
      "field": "study.lab_address",
      "value": "test"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(1, $t->Repertoire);
        $lab_address = data_get($t, 'Repertoire.0.study.lab_address');
        $this->assertStringContainsString('test', $lab_address);
    }

    /** @testx */
    public function deep_and_operators()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "and",
    "content": [
      {
        "op": "and",
        "content": [
          {
            "op": "<",
            "content": {
              "field": "sample.cell_number",
              "value": 10000
            }
          },
          {
            "op": "=",
            "content": {
              "field": "sample.pcr_target.pcr_target_locus",
              "value": "CDR3"
            }
          }
        ]
      },
      {
        "op": "and",
        "content": [
          {
            "op": "<",
            "content": {
              "field": "sample.cell_number",
              "value": 10000
            }
          },
          {
            "op": "=",
            "content": {
              "field": "sample.pcr_target.pcr_target_locus",
              "value": "CDR3"
            }
          },
          {
            "op": "and",
            "content": [
              {
                "op": "and",
                "content": [
                  {
                    "op": "<",
                    "content": {
                      "field": "sample.cell_number",
                      "value": 10000
                    }
                  },
                  {
                    "op": "=",
                    "content": {
                      "field": "sample.pcr_target.pcr_target_locus",
                      "value": "CDR3"
                    }
                  }
                ]
              },
              {
                "op": "and",
                "content": [
                  {
                    "op": "<",
                    "content": {
                      "field": "sample.cell_number",
                      "value": 10000
                    }
                  },
                  {
                    "op": "=",
                    "content": {
                      "field": "sample.pcr_target.pcr_target_locus",
                      "value": "CDR3"
                    }
                  }
                ]
              }
            ]
          }
        ]
      }
    ]
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(1, $t->Repertoire);
        $cell_number = data_get($t, 'Repertoire.0.sample.0.cell_number');
        $this->assertEquals(10000, $cell_number);

        $pcr_target_locus = data_get($t, 'Repertoire.0.sample.0.pcr_target.0.pcr_target_locus');
        $this->assertEquals('CDR3', $pcr_target_locus);
    }

    /** @test */
    public function equals_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "sample.pcr_target.pcr_target_locus",
      "value": "CDR3"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(2, $t->Repertoire);

        $pcr_target_locus = data_get($t, 'Repertoire.0.sample.0.pcr_target.0.pcr_target_locus');
        $this->assertEquals('CDR3', $pcr_target_locus);
    }

    /** @test */
    public function exclude_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "exclude",
    "content": {
      "field": "repertoire_id",
      "value": [
        "8",
        "10",
        "11"
      ]
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(1, $t->Repertoire);

        $repertoire_id = data_get($t, 'Repertoire.0.repertoire_id');
        $this->assertEquals('9', $repertoire_id);
    }

    /** @test */
    public function facets1()
    {
        $s = <<<'EOT'
{
    "facets":"sample.pcr_target.pcr_target_locus"
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(1, $t->Facet);

        $facet = data_get($t, 'Facet.0');
        $this->assertEquals($facet->pcr_target_locus, 'CDR3');
        $this->assertEquals($facet->count, 2);
    }

    /** @test */
    public function facets2()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "sample.pcr_target.pcr_target_locus",
      "value": "CDR3"
    }
  },
  "facets": "subject.subject_id"
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $this->assertCount(2, $t->Facet);

        $this->assertContains(data_get($t, 'Facet.0.subject_id'), ['14711_CSF', '26712_CSF']);
        $this->assertContains(data_get($t, 'Facet.1.subject_id'), ['14711_CSF', '26712_CSF']);
        $this->assertEquals(data_get($t, 'Facet.0.count'), 1);
        $this->assertEquals(data_get($t, 'Facet.1.count'), 1);
    }

    /** @test */
    public function organism_value_filter()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "subject.organism.value",
      "value": "Homo sapiens"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(2, $t->Repertoire);

        $organism_value = data_get($t, 'Repertoire.0.subject.organism.value');
        $this->assertEquals('Homo sapiens', $organism_value);
    }

    /** @test */
    public function pcr_target_locus_filter()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "sample.pcr_target.pcr_target_locus",
      "value": "CDR3"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(2, $t->Repertoire);

        $pcr_target_locus = data_get($t, 'Repertoire.0.sample.0.pcr_target.0.pcr_target_locus');
        $this->assertEquals('CDR3', $pcr_target_locus);
    }

    /** @test */
    public function float_from()
    {
        $s = <<<'EOT'
{
  "from": 10.5
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(2, $t->Repertoire);
    }

    /** @test */
    public function float_size()
    {
        $s = <<<'EOT'
{
  "size": 10.5
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(2, $t->Repertoire);
    }

    /** @test */
    public function greater_than_equals_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": ">=",
    "content": {
      "field": "sample.total_reads_passing_qc_filter",
      "value": 5000
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(1, $t->Repertoire);

        $s = <<<'EOT'
{
  "filters": {
    "op": ">=",
    "content": {
      "field": "sample.total_reads_passing_qc_filter",
      "value": 4854
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(2, $t->Repertoire);
    }

    /** @test */
    public function greater_than_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": ">",
    "content": {
      "field": "sample.total_reads_passing_qc_filter",
      "value": 4854
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(1, $t->Repertoire);
    }

    /** @test */
    public function in_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "in",
    "content": {
      "field": "repertoire_id",
      "value": [
        "7",
        "8",
        "9"
      ]
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(2, $t->Repertoire);

        $s = <<<'EOT'
{
  "filters": {
    "op": "in",
    "content": {
      "field": "repertoire_id",
      "value": [
        "1",
        "8",
        "15"
      ]
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(1, $t->Repertoire);
    }

    /** @test */
    public function is_operator()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "is",
    "content": {
      "field": "sample.cell_number"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);
        $this->assertCount(0, $t->Repertoire);
    }

    /** @test */
    public function sex_filter_male()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "subject.sex",
      "value": "Male"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        // has exactly 1 sample
        $this->assertCount(1, $t->Repertoire);

        // male sample
        $sex = data_get($t, 'Repertoire.0.subject.sex');
        $this->assertEquals($sex, 'Male');
    }

    /** @test */
    // IR-1509 - Age searches don't work
    public function age_filter()
    {
        $s = <<<'EOT'
{
    "filters": {
        "op": "and",
        "content": [
            {
                "op": ">=",
                "content": {
                    "field": "subject.age_min",
                    "value": 15
                }
            },
            {
                "op": "<=",
                "content": {
                    "field": "subject.age_max",
                    "value": 25
                }
            },
            {
                "op": "contains",
                "content": {
                    "field": "subject.age_unit.value",
                    "value": "year"
                }
            }
        ]
    }
} 
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        // has exactly 1 sample
        $this->assertCount(1, $t->Repertoire);

        // check age min value
        $age_min = data_get($t, 'Repertoire.0.subject.age_min');
        $this->assertEquals($age_min, 20);

        // check age max value
        $age_max = data_get($t, 'Repertoire.0.subject.age_max');
        $this->assertEquals($age_max, 20);
    }

    /** @test */
    public function data_processing_array()
    // IR-1542 - No data displayed from the data processing block of repertoire metadata
    {
        $s = <<<'EOT'
{}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $data_processing = data_get($t, 'Repertoire.0.data_processing');
        $this->assertIsArray($data_processing);
        $this->assertCount(1, $data_processing);
    }

    /** @test */
    public function subject_diagnosis_array()
    // IR-1540 - None of the disease diagnosis fields are displaying
    {
        $s = <<<'EOT'
{}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $diagnosis = data_get($t, 'Repertoire.0.subject.diagnosis');
        $this->assertIsArray($diagnosis);
        $this->assertCount(1, $diagnosis);
    }

    /** @test */
    public function subject_synthetic_boolean()
    // IR-1541 - single_cell_sort boolean displaying as 0
    {
        $s = <<<'EOT'
{}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $synthetic = data_get($t, 'Repertoire.0.subject.synthetic');
        $this->assertIsBool($synthetic);
    }

    /** @test */
    public function cell_subset_value()
    // IR-1537 - cell_subset field not displaying on Gateway
    {
        $s = <<<'EOT'
{}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        $cell_subset_value = data_get($t, 'Repertoire.0.sample.0.cell_subset.value');
        $this->assertEquals($cell_subset_value, 'Peripheral blood mononuclear cells');
    }

    // TODO - why this doesn't work?

    /** @testx */
    public function repertoire_id()
    {
        $s = <<<'EOT'
{
  "filters": {
    "op": "=",
    "content": {
      "field": "repertoire_id",
      "value": "8"
    }
  }
}
EOT;
        $response = $this->postJsonString('/airr/v1/repertoire', $s);
        $json = $response->content();
        $t = json_decode($json);

        dd($t);

        $this->assertCount(1, $t->Repertoire);
        $repertoire_id = data_get($t, 'Repertoire.0.repertoire_id');
        $this->assertEquals('8', $repertoire_id);
    }
}

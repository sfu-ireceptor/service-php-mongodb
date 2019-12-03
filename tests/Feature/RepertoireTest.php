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
}

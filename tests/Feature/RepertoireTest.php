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
    public function valid_JSON()
    {
        $response = $this->postJson('/airr/v1/repertoire');
        $response->assertStatus(200);

        $response->assertJson([]);
    }

    /** @test */
    public function correct_JSON()
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
    public function sex_filter_female()
    {
        $s = '{"filters": {"op": "=","content":{"field": "subject.sex","value": "Female"}}}';
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
        $s = '{"filters": {"op": "=","content":{"field": "subject.sex","value": "Male"}}}';
        $response = $this->postJsonString('/airr/v1/repertoire', $s);

        $json = $response->content();
        $t = json_decode($json);

        // has exactly 1 sample
        $this->assertCount(1, $t->Repertoire);

        // male sample
        $sex = data_get($t, 'Repertoire.0.subject.sex');
        $this->assertEquals($sex, 'Male');
    }
}

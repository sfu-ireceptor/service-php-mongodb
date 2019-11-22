<?php

namespace Tests\Feature;

use Tests\TestCase;
use SamplesCollectionSeeder;

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

        $response->assertJson(['Info' => []]);
        $response->assertJson(['Repertoire' => []]);
    }

    /** @test */
    public function sex_filter()
    {
    	$s = '{"filters": {"op": "=","content":{"field": "subject.sex","value": "Female"}}}';

    	$response = $this->postJsonString('/airr/v1/repertoire', $s);
        $response->assertStatus(200);

        $response->assertJson(['Info' => []]);
        $response->assertJson(['Repertoire' => []]);
    }
}

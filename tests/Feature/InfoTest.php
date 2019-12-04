<?php

namespace Tests\Feature;

use Tests\TestCase;

class InfoTest extends TestCase
{
    /** @test */
    public function root()
    {
        $response = $this->getJson('/');
        $response->assertStatus(200);

		$response->assertSeeText('About iReceptor');
    }

    /** @test */
    public function heartbeat()
    {
        $response = $this->postJson('/airr/v1');
        $response->assertStatus(200);

        $response->assertExactJson(['result' => 'success']);
    }

    /** @test */
    public function info()
    {
        $response = $this->postJson('/airr/v1/info');
        $response->assertStatus(200);

         $response->assertJsonStructure([
            'name',
            'version'
        ]);
    }

    /** @test */
    public function swagger()
    {
        $response = $this->postJson('/airr/v1/swagger');
        $response->assertStatus(200);
    }
}

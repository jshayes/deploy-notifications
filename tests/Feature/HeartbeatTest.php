<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use GuzzleHttp\Client as GuzzleClient;

class HeartbeatTest extends TestCase
{
    private $requestData = [
        'icon_emoji' => ':rocket:',
        'color' => '#F35A00',
        'fields' => [
            [
                'title' => 'Project',
                'value' => '<https://envoyer.io/projects/123456|Project>',
                'short' => true,
            ],
            [
                'title' => 'Last Check-In',
                'value' => '10 minutes ago',
                'short' => true,
            ],
        ],
        'text' => 'We haven\'t heard from Heartbeat [socials:refresh-expiring] in a while!',
        'username' => 'Envoyer',
    ];

    private $transformedData = [
        'icon_emoji' => ':rocket:',
        'color' => '#F35A00',
        'fields' => [
            [
                'title' => 'Project',
                'value' => '<https://envoyer.io/projects/123456|Project>',
                'short' => true,
            ],
            [
                'title' => 'Last Check-In',
                'value' => '10 minutes ago',
                'short' => true,
            ],
        ],
        'text' => '<!channel> We haven\'t heard from Heartbeat [socials:refresh-expiring] in a while!',
        'username' => 'Envoyer',
    ];

    private function assertMessageIsSent($input, $expected)
    {
        $guzzleClient = Mockery::mock(GuzzleClient::class);

        $guzzleClient->shouldReceive('post')
            ->with(Mockery::type('string'), Mockery::on(function ($data) use ($expected) {
                $this->assertEquals([
                    'json' => json_decode($expected, true),
                ], $data);

                return true;
            }));

        $this->app->instance(GuzzleClient::class, $guzzleClient);

        $response = $this->postJson('/api/notify', json_decode($input, true));

        $response->assertStatus(200);
    }

    private function getRequestData(): string
    {
        return json_encode($this->requestData);
    }

    private function getTransformedData(): string
    {
        return json_encode($this->transformedData);
    }

    public function testSuccessfulMessage()
    {
        $input = $this->getRequestData();
        $expected = $this->getTransformedData();

        $this->assertMessageIsSent($input, $expected);
    }
}

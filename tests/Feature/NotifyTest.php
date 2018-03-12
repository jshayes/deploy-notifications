<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\GithubClient;
use GuzzleHttp\Client as GuzzleClient;

class NotifyTest extends TestCase
{
    private $requestData = [
        'color' => '#7CD197',
        'icon_emoji' => ':rocket:',
        'fields' => [
            [
                'title' => 'Project',
                'value' => '<https://envoyer.io/projects/123456|Project>',
                'short' => true
            ],
            [
                'title' => 'Commit',
                'value' => '<https://github.com/user/repo/commit/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>',
                'short' => true
            ],
            [
                'title' => 'Committer',
                'value' => 'Jaspaul Bola',
                'short' => true
            ],
            [
                'title' => 'Branch',
                'value' => 'master',
                'short' => true
            ],
        ],
        'text' => 'Project deployed successfully! (https://envoyer.io/projects/123456/deployments/1)',
        'username' => 'Envoyer'
    ];

    private $transformedData = [
        'icon_emoji' => ':rocket:',
        'attachments' => [
            [
                'color' => '#7CD197',
                'fields' => [
                    [
                        'title' => 'Project',
                        'value' => '<https://envoyer.io/projects/123456|Project>',
                        'short' => true
                    ],
                    [
                        'title' => 'Commit',
                        'value' => '<https://github.com/user/repo/commit/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>',
                        'short' => true
                    ],
                    [
                        'title' => 'Committer',
                        'value' => 'Jaspaul Bola',
                        'short' => true
                    ],
                    [
                        'title' => 'Branch',
                        'value' => 'master',
                        'short' => true
                    ],
                    [
                        'title' => 'Message',
                        'value' => 'Message test.',
                        'short' => false
                    ],
                ],
            ],
        ],
        'text' => 'Project deployed successfully! (https://envoyer.io/projects/123456/deployments/1)',
        'username' => 'Envoyer',
    ];

    private function assertMessageIsSent($input, $expected)
    {
        $guzzleClient = Mockery::mock(GuzzleClient::class);
        $githubClient = Mockery::mock(GithubClient::class);

        $githubClient->shouldReceive('getCommit')
            ->andReturn([
                'commit' => [
                    'message' => 'Message test.',
                ],
            ]);

        $guzzleClient->shouldReceive('post')
            ->with(Mockery::type('string'), Mockery::on(function ($data) use ($expected) {
                $this->assertEquals([
                    'json' => json_decode($expected, true)
                ], $data);

                return true;
            }));


        $this->app->instance(GuzzleClient::class, $guzzleClient);
        $this->app->instance(GithubClient::class, $githubClient);

        $response = $this->postJson('/api/notify', json_decode($input, true));

        $response->assertStatus(200);
    }

    private function encode(array $input): string
    {
        return json_encode($input);
    }

    private function getRequestData(): string
    {
        return json_encode($this->requestData);
    }

    private function getRequestDataWithout(string ...$keys): string
    {
        $data = $this->requestData;
        array_forget($data, $keys);
        return json_encode($data);
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

    public function testMissingColour()
    {
        $input = $this->getRequestDataWithout('color');
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }

    public function testMissingFields()
    {
        $input = $this->getRequestDataWithout('fields');
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }

    public function testMissingFieldTitle()
    {
        $input = $this->getRequestDataWithout('fields.0.title');
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }

    public function testMissingFieldValue()
    {
        $input = $this->getRequestDataWithout('fields.0.value');
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }

    public function testInvalidMessage()
    {
        $input = '{"wat":"wat"}';
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }
}

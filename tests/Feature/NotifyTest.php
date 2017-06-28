<?php

namespace Tests\Feature;

use Mockery;
use Tests\TestCase;
use App\GithubClient;
use GuzzleHttp\Client as GuzzleClient;

class NotifyTest extends TestCase
{
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

    public function testSuccessfulMessage()
    {
        $input = '{"color":"#7CD197","icon_emoji":":rocket:","fields":[{"title":"Project","value":"<https:\/\/envoyer.io\/projects\/22967|GoodTalk-Notifications>","short":true},{"title":"Commit","value":"<https:\/\/github.com\/SoapBox\/GoodTalk-Notifications\/commit\/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>","short":true},{"title":"Committer","value":"Jaspaul Bola","short":true},{"title":"Branch","value":"master","short":true}],"text":"Project deployed successfully! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752658)","username":"Envoyer"}';
        $expected = '{"icon_emoji":":rocket:","attachments":[{"color":"#7CD197","fields":[{"title":"Project","value":"<https:\/\/envoyer.io\/projects\/22967|GoodTalk-Notifications>","short":true},{"title":"Commit","value":"<https:\/\/github.com\/SoapBox\/GoodTalk-Notifications\/commit\/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>","short":true},{"title":"Committer","value":"Jaspaul Bola","short":true},{"title":"Branch","value":"master","short":true},{"title":"Message","value":"Message test.","short":false}]}],"text":"Project deployed successfully! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752658)","username":"Envoyer"}';

        $this->assertMessageIsSent($input, $expected);
    }

    public function testFailureMessage()
    {
        $input = '{"color":"#F35A00","icon_emoji":":rocket:","fields":[{"title":"Project","value":"<https:\/\/envoyer.io\/projects\/22967|GoodTalk-Notifications>","short":true},{"title":"Commit","value":"<https:\/\/github.com\/SoapBox\/GoodTalk-Notifications\/commit\/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>","short":true},{"title":"Committer","value":"Jaspaul Bola","short":true},{"title":"Branch","value":"master","short":true}],"text":"Project failed to deploy! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752767)","username":"Envoyer"}';
        $expected = '{"icon_emoji":":rocket:","attachments":[{"color":"#F35A00","fields":[{"title":"Project","value":"<https:\/\/envoyer.io\/projects\/22967|GoodTalk-Notifications>","short":true},{"title":"Commit","value":"<https:\/\/github.com\/SoapBox\/GoodTalk-Notifications\/commit\/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>","short":true},{"title":"Committer","value":"Jaspaul Bola","short":true},{"title":"Branch","value":"master","short":true},{"title":"Message","value":"Message test.","short":false}]}],"text":"Project failed to deploy! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752767)","username":"Envoyer"}';

        $this->assertMessageIsSent($input, $expected);
    }

    public function testMissingColour()
    {
        $input = '{"icon_emoji":":rocket:","fields":[{"title":"Project","value":"<https:\/\/envoyer.io\/projects\/22967|GoodTalk-Notifications>","short":true},{"title":"Commit","value":"<https:\/\/github.com\/SoapBox\/GoodTalk-Notifications\/commit\/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>","short":true},{"title":"Committer","value":"Jaspaul Bola","short":true},{"title":"Branch","value":"master","short":true}],"text":"Project deployed successfully! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752658)","username":"Envoyer"}';
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }

    public function testMissingFields()
    {
        $input = '{"color":"#7CD197","icon_emoji":":rocket:","text":"Project deployed successfully! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752658)","username":"Envoyer"}';
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }

    public function testMissingFieldTitle()
    {
        $input = '{"color":"#7CD197","icon_emoji":":rocket:","fields":[{"value":"<https:\/\/envoyer.io\/projects\/22967|GoodTalk-Notifications>","short":true},{"title":"Commit","value":"<https:\/\/github.com\/SoapBox\/GoodTalk-Notifications\/commit\/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>","short":true},{"title":"Committer","value":"Jaspaul Bola","short":true},{"title":"Branch","value":"master","short":true}],"text":"Project deployed successfully! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752658)","username":"Envoyer"}';
        $expected = $input;

        $this->assertMessageIsSent($input, $expected);
    }

    public function testMissingFieldValue()
    {
        $input = '{"color":"#7CD197","icon_emoji":":rocket:","fields":[{"title":"Project","short":true},{"title":"Commit","value":"<https:\/\/github.com\/SoapBox\/GoodTalk-Notifications\/commit\/ec7cee5c65f23fb9ac6027ef9fa385001484d9b9|ec7cee5>","short":true},{"title":"Committer","value":"Jaspaul Bola","short":true},{"title":"Branch","value":"master","short":true}],"text":"Project deployed successfully! (https:\/\/envoyer.io\/projects\/22967\/deployments\/1752658)","username":"Envoyer"}';
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

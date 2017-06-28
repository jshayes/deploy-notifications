<?php

namespace App;

use Github\Client;

class GithubClient
{
    public function __construct()
    {
        $client = new Client();
        $client->authenticate(env('GITHUB_TOKEN'), Client::AUTH_HTTP_TOKEN);
    }

    public function getCommit($user, $repo, $commit)
    {
        return $client->api('repo')->commits()->show($user, $repo, $commit);

    }
}

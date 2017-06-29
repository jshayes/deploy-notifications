<?php

namespace App;

use Github\Client;

class GithubClient
{
    public function __construct()
    {
        $this->client = new Client();
        $this->client->authenticate(env('GITHUB_TOKEN'), Client::AUTH_HTTP_TOKEN);
    }

    public function getCommit($user, $repo, $commit)
    {
        return $this->client->api('repo')->commits()->show($user, $repo, $commit);

    }
}

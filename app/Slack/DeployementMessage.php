<?php

namespace App\Slack;

use App\GithubClient;
use Illuminate\Support\Facades\Validator;

class DeployementMessage extends Message
{
    protected function initialize()
    {
        $this->fields->push(new Field([
            'title' => 'Message',
            'value' => $this->getCommitMessage(),
            'short' => false,
        ]));

        if ($this->isFailureMessage()) {
            $this->data['text'] = '<!channel> ' . $this->data['text'];
        }
    }

    private function getCommitUrl(): string
    {
        return $this->fields->filter(function ($field) {
            return $field->getTitle() == 'Commit';
        })->map(function ($field) {
            return $field->getValue();
        })->first();
    }

    private function getCommitMessage(): string
    {
        $url = $this->getCommitUrl();

        $githubClient = app(GithubClient::class);
        $matches = [];
        if (preg_match('/^.*github\.com\/([^\/]+)\/([^\/]+)\/commit\/([^\/\|]+).*/', $url, $matches)) {
            list($match, $user, $repo, $commit) = $matches;
        } else {
            return $decodedContent;
        }

        $response = $githubClient->getCommit($user, $repo, $commit);

        Validator::make($response, ['commit.message' => 'required'])->validate();

        return $response['commit']['message'];
    }
}

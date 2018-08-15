<?php

namespace App\Slack;

use App\GithubClient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Support\Arrayable;

class Message implements Arrayable
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;

        $rules = [
            'color' => 'required',
            'fields' => 'required|array',
            'fields.*' => 'array',
        ];

        Validator::make($data, $rules)->validate();

        $this->fields = (new Collection($data['fields']))
            ->map(function ($field) {
                return new Field($field);
            });

        $this->fields->push(new Field([
            'title' => 'Message',
            'value' => $this->getCommitMessage(),
            'short' => false,
        ]));
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

    public function toArray(): array
    {
        $data = $this->data;
        $data['fields'] = $this->fields->toArray();
        return $data;
    }
}

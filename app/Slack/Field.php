<?php

namespace App\Slack;

use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Support\Arrayable;

class Field implements Arrayable
{
    private $data;

    public function __construct(array $data)
    {
        $rules = [
            'title' => 'required',
            'value' => 'required',
        ];

        Validator::make($data, $rules)->validate();

        $this->data = $data;
    }

    public function getTitle(): string
    {
        return $this->data['title'];
    }

    public function getValue(): string
    {
        return $this->data['value'];
    }

    public function toArray(): array
    {
        return $this->data;
    }
}

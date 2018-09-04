<?php

namespace App\Slack;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Arrayable;

abstract class Message implements Arrayable
{
    protected $data;
    protected $fields;

    public function __construct(array $data)
    {
        $this->data = $data;

        $this->fields = (new Collection($data['fields']))
            ->map(function ($field) {
                return new Field($field);
            });

        $this->initialize();
    }

    public function toArray(): array
    {
        $data = $this->data;
        $data['fields'] = $this->fields->toArray();
        return $data;
    }

    abstract protected function initialize();

    protected function isFailureMessage(): bool
    {
        return $this->data['color'] == '#F35A00';
    }
}

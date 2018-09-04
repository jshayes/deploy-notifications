<?php

namespace App\Slack;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class Factory
{
    public static function makeMessage(array $data)
    {
        $rules = [
            'color' => 'required',
            'fields' => 'required|array',
            'fields.*' => 'array',
        ];

        Validator::make($data, $rules)->validate();

        $fields = (new Collection($data['fields']))
            ->map(function ($field) {
                return new Field($field);
            });

        if (self::isDeploymentMessage($fields)) {
            return new DeployementMessage($data);
        } elseif (self::isHeartbeatMessage($fields)) {
            return new HeartbeatMessage($data);
        }

        throw new Exception('Unsupported message type.');
    }

    private static function isDeploymentMessage(Collection $fields): bool
    {
        return $fields->filter(function ($field) {
            return $field->getTitle() == 'Commit';
        })->isNotEmpty();
    }

    private static function isHeartbeatMessage(Collection $fields): bool
    {
        return $fields->filter(function ($field) {
            return $field->getTitle() == 'Last Check-In';
        })->isNotEmpty();
    }
}

<?php

namespace App\Slack;

class HeartbeatMessage extends Message
{
    protected function initialize()
    {
        if ($this->isFailureMessage()) {
            $this->data['text'] = '<!channel> ' . $this->data['text'];
        }
    }
}

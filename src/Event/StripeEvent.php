<?php

namespace App\Event;

use Stripe\ApiResource;
use Stripe\Event;
use Symfony\Contracts\EventDispatcher\Event as BaseEvent;

class StripeEvent extends BaseEvent
{
    public function __construct(
        private readonly Event $event
    ) {
    }

    public function getName(): string
    {
        return $this->event->type;
    }

    public function getResource(): ApiResource
    {
        return $this->event->data->object;
    }
}

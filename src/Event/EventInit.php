<?php
declare(strict_types=1);

namespace Rowbot\DOM\Event;

class EventInit
{
    public $bubbles;
    public $cancelable;
    public $composed;

    public function __construct()
    {
        $this->bubbles = false;
        $this->cancelable = false;
        $this->composed = false;
    }
}

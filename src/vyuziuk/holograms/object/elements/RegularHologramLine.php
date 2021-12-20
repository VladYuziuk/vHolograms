<?php

declare(strict_types=1);

namespace vyuziuk\holograms\object\elements;

use vyuziuk\holograms\object\Hologram;
use vyuziuk\holograms\object\HologramLine;

class RegularHologramLine extends HologramLine
{
    public function __construct(string $text, bool $translate = false)
    {
        parent::__construct($text, $translate);
        $this->setType(Hologram::LINE_TYPE_REGULAR);
    }
}
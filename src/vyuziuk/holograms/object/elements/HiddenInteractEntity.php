<?php

declare(strict_types=1);

namespace vyuziuk\holograms\object\elements;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use vyuziuk\holograms\object\HologramLine;

class HiddenInteractEntity extends Entity
{
    const NETWORK_ID = EntityIds::SLIME;

    public $width = 0.094;
    public $height = 0.438;

    /**
     * @param  Level  $level
     * @param  CompoundTag  $nbt
     * @param  HologramLine  $line
     */
    public function __construct(Level $level, CompoundTag $nbt, private HologramLine $line)
    {
        parent::__construct($level, $nbt);

        $this->setScale(0.1);
        $this->setInvisible(true);
    }

    /**
     * @return HologramLine
     */
    public function getLine(): HologramLine
    {
        return $this->line;
    }
}
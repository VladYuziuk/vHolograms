<?php

declare(strict_types=1);

namespace vyuziuk\holograms\object;

use pocketmine\block\BlockIds;
use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\level\Location;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\Player;
use pocketmine\utils\UUID;
use vyuziuk\messages\Messages;

abstract class HologramLine
{
    /**
     * @var string
     */
    private string $type;

    /**
     * @var int
     */
    private int $entityId;

    /**
     * @param  string  $text
     * @param  bool  $translate
     */
    public function __construct(private string $text, private bool $translate = false)
    {
        if ($translate) {
            Messages::translate($text);
        }

        $this->entityId = Entity::$entityCount++;
    }

    /**
     * @param  Player[]  $players
     */
    public function send(array $players, Location $location): void
    {
        $pk = new AddPlayerPacket;
        $pk->uuid = UUID::fromRandom();
        $pk->entityRuntimeId = $this->entityId;
        $pk->username = $this->text;
        $pk->position = $location;
        $pk->item = ItemStackWrapper::legacy(ItemFactory::get(BlockIds::AIR, 0, 0));
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 1 << Entity::DATA_FLAG_IMMOBILE],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
        ];

        foreach ($players as $player) {
            $player->sendDataPacket($pk);
        }
    }

    /**
     * @param  Player[]  $players
     */
    public function update(array $players): void
    {
        $pk = new SetActorDataPacket();
        $pk->entityRuntimeId = $this->entityId;
        $flags = ((1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) | (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) | (1 << Entity::DATA_FLAG_IMMOBILE));
        $pk->metadata = [
            Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $this->text],
            Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0.0],
        ];

        foreach ($players as $player) {
            $player->sendDataPacket($pk);
        }
    }

    /**
     * @param  Player[]  $players
     */
    public function remove(array $players): void
    {
        $pk = new RemoveActorPacket;
        $pk->entityUniqueId = $this->entityId;

        foreach ($players as $player) {
            $player->sendDataPacket($pk);
        }
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param  string  $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param  string  $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isTranslate(): bool
    {
        return $this->translate;
    }
}
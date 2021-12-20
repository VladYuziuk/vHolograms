<?php

declare(strict_types=1);

namespace vyuziuk\holograms;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use vyuziuk\holograms\command\HologramsCommand;
use vyuziuk\holograms\object\elements\HiddenInteractEntity;
use vyuziuk\holograms\object\elements\InteractableHologramLine;
use vyuziuk\holograms\object\Hologram;

class Holograms extends PluginBase implements Listener
{
    use SingletonTrait;

    private Hologram $hologram;

    public function onLoad()
    {
        self::$instance = $this;
    }

    public function onEnable()
    {
        HologramsManager::getInstance();

        $this->getServer()->getCommandMap()->register('hologram', new HologramsCommand('hologram', '', '', ['holo']));

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onDisable()
    {
        HologramsManager::getInstance()->save();
    }

    /**
     * @param  PlayerJoinEvent  $event
     * @priority MONITOR
     */
    public function handleJoin(PlayerJoinEvent $event): void
    {
        foreach (HologramsManager::getInstance()->getHolograms() as $hologram) {
            $hologram->send([$event->getPlayer()]);
        }
    }

    /**
     * @param  EntityDamageEvent  $event
     * @priority MONITOR
     */
    public function handleAttack(EntityDamageEvent $event): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $entity = $event->getEntity();

            if ($damager instanceof Player) {
                if ($entity instanceof HiddenInteractEntity) {
                    $line = $entity->getLine();

                    if ($line instanceof InteractableHologramLine) {
                        if ($line->onClick !== null) {
                            ($line->onClick)($damager);
                        }else {
                            if ($line->getMessage() !== "") {
                                $damager->sendMessage($line->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }
}
<?php

declare(strict_types=1);

namespace vyuziuk\holograms\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use vyuziuk\holograms\HologramsManager;
use vyuziuk\holograms\object\Hologram;

class HologramsCommand extends Command
{
    /**
     * @param  CommandSender  $sender
     * @param  string  $commandLabel
     * @param  array  $args
     * @return mixed|void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("Command is only available in game.");
            return;
        }

        if (!isset($args[0])) {
            $this->sendHelpMessage($sender);
            return;
        }

        switch ($args[0]) {

            case 'help':
                $this->sendHelpMessage($sender);
                break;

            case 'add':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram add <name>");
                    return;
                }

                if (HologramsManager::getInstance()->get($args[1]) !== null) {
                    $sender->sendMessage(TextFormat::RED."Hologram with this name already exists.");
                    return;
                }

                $hologram = new Hologram($args[1], $sender->getLocation());
                HologramsManager::getInstance()->add($hologram);

                $sender->sendMessage(TextFormat::GREEN."New hologram created!");

                break;

            case 'remove':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram remove <name>");
                    return;
                }

                if (HologramsManager::getInstance()->get($args[1]) === null) {
                    $sender->sendMessage(TextFormat::RED."Hologram with this name does not exist.");
                    return;
                }

                HologramsManager::getInstance()->remove($args[1]);

                $sender->sendMessage(TextFormat::GREEN."Hologram was removed!");

                break;

            case 'list':
                $holograms = "";

                foreach (HologramsManager::getInstance()->getHolograms() as $hologram) {
                    $holograms .= "\n - ".$hologram->getName();
                }

                $sender->sendMessage(sprintf(TextFormat::GREEN."All holograms: %s", $holograms));

                break;

            case 'addline':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram addline <hologram name> <interactable/regular> <text> <message?>");
                    return;
                }

                if (!isset($args[2])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram addline <hologram name> <interactable/regular> <text> <message?>");
                    return;
                }

                if (!isset($args[3])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram addline <hologram name> <interactable/regular> <text> <message?>");
                    return;
                }

                $hologram = HologramsManager::getInstance()->get($args[1]);

                switch ($args[2]) {
                    case Hologram::LINE_TYPE_INTERACTABLE:
                    case 'i':
                        if (!isset($args[4])) {
                            $sender->sendMessage(TextFormat::RED."Usage: /hologram addline <hologram name> <interactable/regular> <text> <message?>");
                            return;
                        }

                        $message = implode(' ', array_slice($args, 4));

                        $hologram->insertInteractableLine([$sender], $args[3], null, $message);
                        break;

                    case Hologram::LINE_TYPE_REGULAR:
                    case 'r':
                        $hologram->insertRegularLine([$sender], $args[3]);
                        break;

                    default:
                        $sender->sendMessage(TextFormat::RED."There is only two types of line: interactable, regular.");
                        break;
                }

                $sender->sendMessage(TextFormat::GREEN."New hologram line was added!");

                break;

            case 'removeline':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram removeline <hologram name> <text>");
                    return;
                }

                if (!isset($args[2])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram removeline <hologram name> <index>");
                    return;
                }

                $hologram = HologramsManager::getInstance()->get($args[1]);
                $hologram->removeLine([$sender], $args[2]);

                $sender->sendMessage(TextFormat::GREEN."Hologram line was removed!");

                break;

            case 'send':
                if (!isset($args[1])) {
                    $sender->sendMessage(TextFormat::RED."Usage: /hologram send <name>");
                    return;
                }

                HologramsManager::getInstance()->get($args[1])->send(Server::getInstance()->getOnlinePlayers());

                $sender->sendMessage(sprintf(TextFormat::GREEN."Hologram %s was sent to all online players!",
                    $args[1]));

                break;

        }
    }

    /**
     * @param  Player  $player
     */
    private function sendHelpMessage(Player $player): void
    {
        $player->sendMessage(TextFormat::AQUA."> Hologram help");
        $player->sendMessage(TextFormat::AQUA."> /hologram help");
        $player->sendMessage(TextFormat::AQUA."> /hologram add <name> - create new hologram");
        $player->sendMessage(TextFormat::AQUA."> /hologram remove <name> - remove hologram");
        $player->sendMessage(TextFormat::AQUA."> /hologram list - list all holograms loaded");
        $player->sendMessage(TextFormat::AQUA."> /hologram addline <hologram name> <interactable/regular> <text> <message?> - add line to hologram");
        $player->sendMessage(TextFormat::AQUA."> /hologram removeline <hologram name> <index> - remove line from the hologram");
        $player->sendMessage(TextFormat::AQUA."> /hologram send <name> - send hologram to all online players");
    }
}
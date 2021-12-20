<?php

declare(strict_types=1);

namespace vyuziuk\holograms;

use Exception;
use pocketmine\level\Location;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use vyuziuk\holograms\object\elements\InteractableHologramLine;
use vyuziuk\holograms\object\elements\RegularHologramLine;
use vyuziuk\holograms\object\Hologram;

class HologramsManager
{
    use SingletonTrait;

    /**
     * @var float
     */
    private float $lineOffset;
    /**
     * @var array|string[]
     */
    private array $notSavable;
    /**
     * @var Hologram[]
     */
    private array $holograms = [];

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $plugin = Holograms::getInstance();
        $plugin->saveResource('holograms.yml');
        $conf = new Config($plugin->getDataFolder().'holograms.yml', Config::YAML);

        $this->lineOffset = $conf->getNested('line-offset');
        $this->notSavable = explode(',', $conf->getNested('not-savable'));

        $holograms = $conf->getNested('holograms');

        $plugin->getLogger()->notice("Loading holograms...");

        $i = 0;
        foreach ($holograms as $name => $data) {
            $location = $this->locationFromJson($data['location']);
            $hologram = new Hologram($name, $location);

            foreach ($data['lines'] as $line) {
                switch ($line['type']) {
                    case Hologram::LINE_TYPE_INTERACTABLE:
                        $hologram->addLine(new InteractableHologramLine($line['text'], null,
                            $line['message'], $line['translate']));
                        break;
                    case Hologram::LINE_TYPE_REGULAR:
                        $hologram->addLine(new RegularHologramLine($line['text'], $line['translate']));
                        break;
                }
            }

            $this->add($hologram);
            $i++;
        }

        $plugin->getLogger()->notice(sprintf("%s holograms loaded", $i));
    }

    /**
     * @param  string  $data
     * @return Location|null
     * @throws Exception
     */
    private function locationFromJson(string $data): ?Location
    {
        $data = json_decode($data, true);

        if (!Holograms::getInstance()->getServer()->isLevelGenerated($data['level'])) {
            throw new Exception(sprintf("Level with name %s is not generated!", $data['level']));
        }

        if (!Holograms::getInstance()->getServer()->isLevelLoaded($data['level'])) {
            Holograms::getInstance()->getServer()->loadLevel($data['level']);
        }

        return new Location($data['x'], $data['y'], $data['z'], 0, 0,
            Server::getInstance()->getLevelByName($data['level']));
    }

    /**
     * @param  Hologram  $hologram
     */
    public function add(Hologram $hologram): void
    {
        $this->holograms[$hologram->getName()] = $hologram;
    }

    /**
     * @param  string  $name
     * @return Hologram|null
     */
    public function get(string $name): ?Hologram
    {
        return $this->holograms[$name] ?? null;
    }

    /**
     * @param  string  $name
     */
    public function remove(string $name): void
    {
        if (isset($this->holograms[$name])) {
            unset($this->holograms[$name]);
        }
    }

    public function save(): void
    {
        Holograms::getInstance()->getLogger()->notice("Saving holograms...");

        $conf = new Config(Holograms::getInstance()->getDataFolder().'holograms.yml', Config::YAML);
        $conf->setNested('holograms', $this->toArray());
        $conf->save();
    }

    /**
     * @return array
     */
    private function toArray(): array
    {
        $holograms = [];

        foreach ($this->holograms as $hologram) {
            if (!in_array($hologram->getName(), $this->notSavable)) {
                $lines = [];

                foreach ($hologram->getLines() as $line) {
                    $lines[] = [
                        'type' => $line->getType(),
                        'translate' => $line->isTranslate(),
                        'text' => $line->getText(),
                        'message' => ($line instanceof InteractableHologramLine) ? $line->getMessage() : ''
                    ];
                }

                $holograms[$hologram->getName()] = [
                    'location' => $this->locationToJson($hologram->getLocation()),
                    'lines' => $lines
                ];
            }
        }
        return $holograms;
    }

    /**
     * @param  Location  $location
     * @return string
     */
    private function locationToJson(Location $location): string
    {
        return json_encode([
            'x' => $location->x,
            'y' => $location->y,
            'z' => $location->z,
            'level' => $location->level->getFolderName()
        ]);
    }

    /**
     * @return Hologram[]
     */
    public function getHolograms(): array
    {
        return $this->holograms;
    }

    /**
     * @return float
     */
    public function getLineOffset(): mixed
    {
        return $this->lineOffset;
    }
}
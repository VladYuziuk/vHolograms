<?php

declare(strict_types=1);

namespace vyuziuk\holograms\object;

use Closure;
use JetBrains\PhpStorm\Pure;
use pocketmine\level\Location;
use pocketmine\Player;
use vyuziuk\holograms\HologramsManager;
use vyuziuk\holograms\object\elements\InteractableHologramLine;
use vyuziuk\holograms\object\elements\RegularHologramLine;

final class Hologram
{
    public const LINE_TYPE_REGULAR = 'regular';
    public const LINE_TYPE_INTERACTABLE = 'interactable';

    public const LINES_PER_PAGE = 2;

    /**
     * @var int
     */
    private int $page;

    /**
     * @var HologramLine[]
     */
    private array $lines = [];

    /**
     * @var HologramLine[]
     */
    private array $linesToSend = [];

    /**
     * @param  string  $name
     * @param  Location  $location
     * @param  bool  $paginated
     */
    public function __construct(private string $name, private Location $location, private bool $paginated = false)
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  HologramLine  $hologramLine
     * @return Hologram
     */
    public function addLine(HologramLine $hologramLine): Hologram
    {
        $this->lines[] = $hologramLine;
        return $this;
    }

    /**
     * @param  Player[]  $players
     * @param  string  $text
     */
    public function insertRegularLine(array $players, string $text): void
    {
        $this->lines[] = new RegularHologramLine($text);
        $this->send($players);
    }

    /**
     * @param  Player[]  $players
     */
    public function send(array $players, int $page = 1): void
    {
        if ($this->paginated) {
            $this->page = $page;

            $linesToSend = array_slice($this->lines, ($page - 1) * self::LINES_PER_PAGE, self::LINES_PER_PAGE);

            if ($page === 1) {
                if ($this->getPages() > 1) {
                    $linesToSend[] = $this->getNextPageLine();
                }
            }else {
                $linesToSend[] = $this->getPreviousPageLine();

                if ($page < $this->getPages()) {
                    $linesToSend[] = $this->getNextPageLine();
                }
            }

            $this->setLinesToSend($linesToSend);
        }else {
            $this->linesToSend = $this->lines;
        }

        $location = clone $this->location;
        foreach ($this->linesToSend as $line) {
            $line->send($players, $location);
            $location->y -= HologramsManager::getInstance()->getLineOffset();
        }
    }

    /**
     * @return int
     */
    #[Pure] public function getPages(): int
    {
        return (int) ceil(count($this->getLines()) / self::LINES_PER_PAGE);
    }

    /**
     * @return HologramLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @return InteractableHologramLine
     */
    public function getNextPageLine(): InteractableHologramLine
    {
        return new InteractableHologramLine("Next Page ->",
            function (Player $player){
                $this->remove([$player]);
                $this->send([$player], $this->page + 1);
            });
    }

    /**
     * @param  array  $players
     */
    public function remove(array $players): void
    {
        foreach ($this->linesToSend as $line) {
            $line->remove($players);
        }
    }

    /**
     * @return InteractableHologramLine
     */
    public function getPreviousPageLine(): InteractableHologramLine
    {
        return new InteractableHologramLine("<- Previous Page",
            function (Player $player){
                $this->remove([$player]);
                $this->send([$player], $this->page - 1);
            });
    }

    /**
     * @param  HologramLine[]  $linesToSend
     */
    public function setLinesToSend(array $linesToSend): void
    {
        $this->linesToSend = $linesToSend;
    }

    /**
     * @return Location
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * @param  array  $players
     * @param  string  $text
     * @param  Closure|null  $onClick
     * @param  string  $message
     */
    public function insertInteractableLine(
        array $players,
        string $text,
        ?Closure $onClick = null,
        string $message = ""
    ): void{
        $this->lines[] = new InteractableHologramLine($text, $onClick, $message);
        $this->send($players);
    }

    /**
     * @param  Player[]  $players
     */
    public function updateLines(array $players): void
    {
        foreach ($this->linesToSend as $line) {
            $line->update($players);
        }
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param  Player[]  $players
     * @param  int  $index
     */
    public function removeLine(array $players, int $index): void
    {
        if (!isset($this->lines[$index])) {
            return;
        }

        // remove line from array and from the level
        $this->lines[$index]->remove($players);
        unset($this->lines[$index]);
    }
}
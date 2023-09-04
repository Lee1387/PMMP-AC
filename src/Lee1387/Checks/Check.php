<?php

namespace Lee1387\Checks;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\AntiCheat;
use Lee1387\User\User;

class Check
{

    /*** @var string */
    private string $name;
    /*** @var int */
    private int $maxViolations;
    /*** @var bool */
    private bool $notify;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;

        $config = AntiCheat::getInstance()->getConfig();
        $this->maxViolations = $config->get($name . "-MaxViolations") == 0 ? false : $config->get($name . "-MaxViolations");
        $this->notify = $config->get($name . "-notify") == null ? false : $config->get($name . "-notify");
    }

    public function onJoin(PlayerJoinEvent $event, User $user) : void {}
    public function onAttack(EntityDamageByEntityEvent $event, User $user) : void {}
    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user) : void {}

    /**
     * @return int
     */
    public function getMaxViolations(): int
    {
        return $this->maxViolations;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param bool $notify
     */
    public function setNotify(bool $notify): void
    {
        $this->notify = $notify;
    }

    /**
     * @return bool
     */
    public function hasNotify(): bool
    {
        return $this->notify;
    }

}
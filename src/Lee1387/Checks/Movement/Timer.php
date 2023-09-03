<?php

namespace Lee1387\Checks\Movement;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\Checks\Punishments;
use Lee1387\AntiCheat;
use Lee1387\User\User;

class Timer extends Check {

    private int $MAX_TICK_DIFFERENCE;

    public function __construct() {
        parent::__construct("Timer");

        $config = AntiCheat::getInstance()->getConfig();
        $this->MAX_TICK_DIFFERENCE = $config->get("Timer-TickDifference") == null ? 10 : $config->get("Timer-TickDifference");
    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void {
        $newTickDelay = AntiCheat::getInstance()->getServer()->getTick() - $packet->getTick();
        $delayDifference = $user->getTickDelay() - $newTickDelay;

        if ($delayDifference >= $this->MAX_TICK_DIFFERENCE) {
            if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                $user->increaseViolation($this->getName(), 1);
            } else {
                Notifier::NotifyFlag($player->getName(), $this->getName(), $user->getViolation($this->getName()), $this->hasNotify());
                Punishments::punishPlayer($player, $this, $user, $player->getPosition(), AntiCheat::getInstance()->getConfig()->get($this->getName() . "-Punishment"));
                $user->setTickDelay($newTickDelay);
            }
        } else {
            $user->decreaseViolation($this->getName(), 1);
        }
    }
}
<?php

namespace Lee1387\Checks\Packets;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\CheckManager;
use Lee1387\Checks\Notifier;
use Lee1387\Checks\Punishments;
use Lee1387\User\User;

class BadPacketsC extends Check 
{

    public function __construct()
    {
        parent::__construct("BadPacketsC", CheckManager::PLAYER);
    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void 
    {

        $player = $user->getPlayer();
        $cache = $user->getCache();

        if (!isset($cache["LastTick"])) {
            $cache["LastTick"] = $packet->getTick();
            $user->setCache($cache);
            return;
        }

        if ($user->getTicksSinceJoin() > 100) {
            if (abs($packet->getTick() - $cache["LastTick"]) > 2) {
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                    $user->increaseViolation($this->getName(), 2);
                }

                if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
                    Punishments::punishPlayer($this, $user, $player->getPosition());
                    Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                }
            }
        }

        $cache["LastTick"] = $packet->getTick();
        $user->setCache($cache);
        
    }
}
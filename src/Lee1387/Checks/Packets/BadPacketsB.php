<?php

namespace Lee1387\Checks\Packets;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\CheckManager;
use Lee1387\Checks\Notifier;
use Lee1387\Checks\Punishments;
use Lee1387\User\User;

class BadPacketsB extends Check 
{

    public function __construct()
    {
        parent::__construct("BadPacketsB", CheckManager::PLAYER);
    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {
        
        $player = $user->getPlayer();

        if ($user->getTicksSinceJoin() > 40){
            $rewindFrame = $user->rewindMovementBuffer();

            if ($packet->getTick() < $rewindFrame->getPlayerTick()){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                    $user->increaseViolation($this->getName(), 2);
                }

                if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
                    Punishments::punishPlayer($this, $user, $player->getPosition());
                    Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                }
            }

        }
        
    }
}
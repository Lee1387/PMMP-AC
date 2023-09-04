<?php

namespace Lee1387\Checks\Packets;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\Checks\Punishments;
use Lee1387\AntiCheat;
use Lee1387\User\User;

class BadPacketsA extends Check
{

    public function __construct()
    {
        parent::__construct("BadPacketsA");
    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void
    {
        foreach ($user->getMovementBuffer() as $moveFrame){
            if ($moveFrame->getPlayerTick() == $packet->getTick()){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                    $user->increaseViolation($this->getName());
                }else{
                    Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                    Punishments::punishPlayer($player, $this, $user, $player->getPosition(), $this->getPunishment());
                }
            }
        }
    }
}
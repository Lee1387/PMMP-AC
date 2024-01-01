<?php

namespace Lee1387\Checks\Combat;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\CheckManager;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\User\User;
use Lee1387\Utils\Random;
use Lee1387\Utils\Rotations;

class ImpossibleRotations extends Check
{

    public function __construct()
    {
        parent::__construct("ImpossibleRotations", CheckManager::COMBAT);
    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {

        $player = $user->getPlayer();
        $delta = abs($user->getOldRotation()->getY() - $user->getRotation()->getY());
        $delta = Rotations::wrapAngleTo180_float($delta);

        if (abs($delta) > 3.5){
            if ($packet->getHeadYaw() == $packet->getYaw()){
                $user->increaseViolation($this->getName(), 1.5);
            }else {
                $user->decreaseViolation($this->getName(), 2);
            }
        }else{
            $user->decreaseViolation($this->getName(), 0.5);
        }

        if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
            Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
            $user->setViolation($this->getName(), Random::clamp(0, ($this->getMaxViolations() * 2), $user->getViolation($this->getName())));
            $user->setPunishNext(true);
        }
    }

}
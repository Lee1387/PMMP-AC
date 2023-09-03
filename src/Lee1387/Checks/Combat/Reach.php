<?php

namespace Lee1387\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\User\User;
use Lee1387\Utils\Constants;
use Lee1387\Utils\Raycast;

class Reach extends Check {

    public function __construct() {
        parent::__construct("Reach", 3);
    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player) {

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = AntiCheat::getInstance()->getUserManager()->getUser($victimUUID);

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 1;

            if (count($victimUser->getMovementBuffer()) <= $rewindTicks || count($user->getMovementBuffer()) <= $rewindTicks) {
                return;
            }

            $rewindBuffer = $victimUser->rewindMovementBuffer($rewindTicks);
            $playerVec = new Vector3($player->getPosition()->getX(), 0 , $player->getPosition()->getZ());
            $victimVec = new Vector3($rewindBuffer->getPosition()->getX(), 0 , $rewindBuffer->getPosition()->getZ());
            $distance = $playerVec->distance($victimVec);

            if ($distance > Constants::ATTACK_REACH) {
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                    $user->increaseViolation($this->getName(), 2);
                }
            } else {
                $user->decreaseViolation($this->getName(), 1);
            }

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
                Notifier::NotifyFlag($player->getName(), $this->getName(), $user->getViolation($this->getName()));
                $event->cancel();
            }
        }
    }
    
}
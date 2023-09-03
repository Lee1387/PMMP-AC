<?php

namespace Lee1387\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\User\User;
use Lee1387\Utils\Constants;
use Lee1387\Utils\Raycast;

class Hitbox extends Check {

    public function __construct() {
        parent::__construct("Hitbox");
    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player) {

            if ($user->getInput() == 0 || $user->getInput() == InputMode::TOUCHSCREEN) {
                return;
            }

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = AntiCheat::getInstance()->getUserManager()->getUser($victimUUID);

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 1;

            if (count($victimUser->getMovementBuffer()) <= $rewindTicks || count($user->getMovementBuffer()) <= $rewindTicks) {
                return;
            }

            $rewindBuffer = $victimUser->rewindMovementBuffer($rewindTicks);
            $ray = Raycast::EntityOnLine($rewindBuffer->getBoundingBox(), $player->getPosition(), $player->getDirectionVector(), $player->getPosition()->distance($victim->getPosition()));

            if (!$ray) {
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                    $user->increaseViolation($this->getName(), 2);
                }
            } else {
                $user->decreaseViolation($this->getName(), 1);
            }

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
                Notifier::NotifyFlag($player->getName(), $this->getName(), $user->getViolation($this->getName()), $this->hasNotify());
                $event->cancel();
            }
        }
    }
    
}
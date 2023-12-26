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
use pocketmine\event\entity\EntityDamageEvent;

class Reach extends Check
{

    private float $MAX_REACH;

    public function __construct()
    {
        parent::__construct("Reach");

        $config = AntiCheat::getInstance()->getConfig();
        $this->MAX_REACH = $config->get("Maximum-Reach") == null ? Constants::ATTACK_REACH : $config->get("Maximum-Reach");

    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $event->getDamager();
        $victim = $event->getEntity();

        if ($player instanceof Player && $victim instanceof Player){

            if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK){
                return;
            }

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = AntiCheat::getInstance()->getUserManager()->getUser($victimUUID);

            $rawPlayerVec = new Vector3($player->getPosition()->getX(), 0, $player->getPosition()->getZ());
            $rawVictimVec = new Vector3($victim->getPosition()->getX(), 0, $victim->getPosition()->getZ());
            $rawDistance = $rawPlayerVec->distance($rawVictimVec);

            if ($rawDistance <= $this->MAX_REACH){
                return;
            }

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 2;

            if (count($victimUser->getMovementBuffer()) <= $rewindTicks || count($user->getMovementBuffer()) <= $rewindTicks){
                return;
            }

            $rewindBuffer = $victimUser->rewindMovementBuffer($rewindTicks);
            $playerVec = new Vector3($player->getPosition()->getX(), 0 , $player->getPosition()->getZ());
            $victimVec = new Vector3($rewindBuffer->getPosition()->getX(), 0 , $rewindBuffer->getPosition()->getZ());
            $distance = $playerVec->distance($victimVec);

            if ($distance > $this->MAX_REACH) {
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                    $user->increaseViolation($this->getName(), 2);
                }
            }else{
                $user->decreaseViolation($this->getName(), 1);
            }

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                $event->cancel();
            }
        }
    }

}
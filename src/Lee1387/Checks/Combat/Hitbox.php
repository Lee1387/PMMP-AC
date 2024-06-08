<?php

namespace Lee1387\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\Checks\CheckManager;
use Lee1387\User\User;
use Lee1387\Utils\Constants;
use Lee1387\Utils\Raycast;
use pocketmine\event\entity\EntityDamageEvent;

class Hitbox extends Check
{

    public function __construct()
    {
        parent::__construct("Hitbox", CheckManager::COMBAT);
    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $user->getPlayer();
        $victim = $event->getEntity();
        $distance = $player->getPosition()->distance($victim->getPosition());

        if ($victim instanceof Player){

            if ($user->getInput() == 0 || $user->getInput() == InputMode::TOUCHSCREEN || $event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK){
                return;
            }

            $ray = Raycast::isBBOnline($victim->getPosition(), $player->getPosition()->add(0, 1.62, 0), $player->getDirectionVector(), 6);
            if ($ray){
                return;
            }

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = AntiCheat::getInstance()->getUserManager()->getUser($victimUUID);

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 3;

            if ($user->getTicksSinceJoin() < 40 || count($user->getMovementBuffer()) <= $rewindTicks){
                return;
            }

            for ($i = 0; $i < $rewindTicks; $i++) {
                $rewindVictim = $victimUser->rewindMovementBuffer($i);
                $rewindVec = Raycast::isBBOnline($rewindVictim->getPosition(), $player->getPosition()->add(0, 1.62, 0), $player->getDirectionVector(), 6);

                $player->sendMessage($player->getPosition()->getY() + 1.62);

            if (!$rewindVec){
                $user->decreaseViolation($this->getName());
                    return;
                }
            }

            if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                $user->increaseViolation($this->getName(), 2);
            }

            $event->cancel();

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
            }
        }
    }

}
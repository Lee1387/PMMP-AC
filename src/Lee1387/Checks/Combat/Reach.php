<?php

namespace Lee1387\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\Checks\CheckManager;
use Lee1387\User\User;
use Lee1387\Utils\Constants;
use Lee1387\Utils\Raycast;

class Reach extends Check
{

    private float $MAX_REACH;

    public function __construct()
    {
        parent::__construct("Reach", CheckManager::COMBAT);

        $config = AntiCheat::getInstance()->getSavedConfig();
        $this->MAX_REACH = $config->get("Maximum-Reach") == null ? Constants::ATTACK_REACH : $config->get("Maximum-Reach");

    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $user->getPlayer();
        $victim = $event->getEntity();

        if ($victim instanceof Player){

            $eligibleGamemode = $player->getGamemode() === GameMode::SURVIVAL() || $player->getGamemode() === GameMode::ADVENTURE();

            if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK || !$eligibleGamemode || $user->getTicksSinceJoin() < 40){
                return;
            }

            $victimUUID = $victim->getUniqueId()->toString();
            $victimUser = AntiCheat::getInstance()->getUserManager()->getUser($victimUUID);

            $rayVec = Raycast::isBBOnline($victim->getPosition(), $player->getPosition(), $player->getDirectionVector(), $this->MAX_REACH);

            if ($rayVec){
                return;
            }

            $ping = $player->getNetworkSession()->getPing();
            $rewindTicks = ceil($ping / 50) + 3;

            $victimPing = $victimUser->getPlayer()->getNetworkSession()->getPing();
            $victimTicks = ceil($victimPing / 50) + 2;

            for ($i = 0; $i < $rewindTicks; $i++) {
                for ($j = 0; $i < $victimTicks; $j++) {
                    $rewindVictim = $victimUser->rewindMovementBuffer($j);
                    $playerXZ = new Vector3($player->getPosition()->getX(), 0, $player->getPosition()->getZ());
                    $victimXZ = new Vector3($rewindVictim->getPosition()->getX(), 0, $rewindVictim->getPosition()->getZ());
                    $distXZ = $playerXZ->distance($victimXZ);
                    $distY = abs($player->getPosition()->getY() - $rewindVictim->getPosition()->getY());

                    if ($distXZ < Constants::ATTACK_REACH && $distY < Constants::ATTACK_REACH + 0.6) {
                        $user->decreaseViolation($this->getName());
                        return;
                    }
                }

            }

            if ($user->getViolation($this->getName()) < $this->getMaxViolations()) {
                $user->increaseViolation($this->getName(), 2);
            }

            $event->cancel();

            if ($user->getViolation($this->getName()) >= $this->getMaxViolations()) {
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
            }
            
        }
    }

}
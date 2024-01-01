<?php

namespace Lee1387\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use Lee1387\Checks\Check;
use Lee1387\Checks\CheckManager;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\User\User;
use Lee1387\Utils\Rotations;

class RotationsA extends Check 
{

    private float $MAX_DIFF = 45;

    public function __construct()
    {
        parent::__construct("RotationsA", CheckManager::COMBAT);
        $config = AntiCheat::getInstance()->getSavedConfig();
        $this->MAX_DIFF = $config->get("Angle-Difference") ?? $this->MAX_DIFF;
    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $user->getPlayer();
        $cache = $user->getCache();

        if (!isset($cache["OldYaw"]) || !isset($cache["LastTick"])){
            $cache["OldYaw"] = 0;
            $cache["LastTick"] = 0;
        }

        $oldYaw = $cache["OldYaw"];
        $lastTick = $cache["LastTick"];
        $currentTick = AntiCheat::getInstance()->getServer()->getTick();

        // $player->sendMessage("TICKS: $lastTick | $currentTick");

        $tickDelta = $currentTick - $lastTick;
        $delta = abs($oldYaw - $user->getRotation()->getY());
        $delta = Rotations::wrapAngleTo180_float($delta);

        //  $player->sendMessage("CHECK: $delta | $tickDelta");

        if ($delta >= $this->MAX_DIFF && $tickDelta <= 6){
            $user->increaseViolation($this->getName());
        }else{
            $user->resetViolation($this->getName());
        }

        $cache["OldYaw"] = $user->getRotation()->getY();
        $cache["LastTick"] = AntiCheat::getInstance()->getServer()->getTick();
        $user->setCache($cache);

        if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
            Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
            $user->setPunishNext(true);
        }

    }

    /* public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {

        $player = $user->getPlayer();

        $delta = abs($packet->getYaw() - $player->getLocation()->getYaw());
        $delta = Rotations::wrapAngleTo180_float($delta);

        $delta2 = abs($packet->getPitch() - $player->getLocation()->getPitch());
        $delta2 = Rotations::wrapAngleTo180_float($delta2);

        if (abs($delta2) <= 0 && abs ($delta) > 0){
           // $player->sendActionBarMessage($delta . " " . $delta2);
        }
        
        if ($user->getViolation($this->getName()) >= $this->getMaxViolations()){
            Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
            $user->setPunishNext(true);
        }
    }*/

}
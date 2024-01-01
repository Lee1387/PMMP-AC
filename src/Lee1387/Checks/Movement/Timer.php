<?php

namespace Lee1387\Checks\Movement;

use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\Checks\Punishments;
use Lee1387\AntiCheat;
use Lee1387\Checks\CheckManager;
use Lee1387\User\User;

class Timer extends Check
{

    private int $MAX_TICK_DIFFERENCE;

    public function __construct()
    {
        parent::__construct("Timer", CheckManager::MOVEMENT);

        $config = AntiCheat::getInstance()->getSavedConfig();
        $this->MAX_TICK_DIFFERENCE = $config->get("Timer-TickDifference") == null ? 10 : $config->get("Timer-TickDifference");

    }

    public function onMove(PlayerAuthInputPacket $packet, User $user): void
    {
        
        $player = $user->getPlayer();

        $serverTps = AntiCheat::getInstance()->getServer()->getTicksPerSecond();
        $serverTick = AntiCheat::getInstance()->getServer()->getTick();
        $newTickDelay = $serverTick - $packet->getTick();
        $delayDifference = $user->getTickDelay() - $newTickDelay;

        if ($user->getTicksSinceJoin() < 200 || $serverTps < 19){
            $serverTick = AntiCheat::getInstance()->getServer()->getTick();
            $newTickDelay = $serverTick - $packet->getTick();
            $user->setTickDelay($newTickDelay);
            return;
        }

        if ((float) $delayDifference >= ($this->MAX_TICK_DIFFERENCE + (abs(20 - $serverTps) * 2))){
            if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                $user->increaseViolation($this->getName(), 1);
            }else{
                Notifier::NotifyFlag($player->getName(), $user, $this, $user->getViolation($this->getName()), $this->hasNotify());
                Punishments::punishPlayer($this, $user, $player->getPosition());
                $user->setTickDelay($newTickDelay);
            }
        }else{
            $user->decreaseViolation($this->getName(), 1);
        }
    }
}
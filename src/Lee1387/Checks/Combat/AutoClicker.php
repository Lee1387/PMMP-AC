<?php

namespace Lee1387\Checks\Combat;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\Checks\CheckManager;
use Lee1387\User\User;
use Lee1387\Utils\Constants;
use pocketmine\event\entity\EntityDamageEvent;

class AutoClicker extends Check
{

    private float $CPS_LIMIT;

    public function __construct()
    {
        parent::__construct("AutoClicker", CheckManager::COMBAT);

        $config = AntiCheat::getInstance()->getSavedConfig();
        $this->CPS_LIMIT = $config->get("CPS-Limit") == null ? Constants::CPS_LIMIT : $config->get("CPS-Limit");

    }

    public function onAttack(EntityDamageByEntityEvent $event, User $user): void
    {
        $player = $event->getDamager();

        if ($event->getCause() !== EntityDamageEvent::CAUSE_ENTITY_ATTACK){
            return;
        }

        if ($player instanceof Player){

            $hits = 0;

            foreach ($user->getAttackBuffer() as $attackFrame){
                if ((AntiCheat::getInstance()->getServer()->getTick() - ($attackFrame->getServerTick() - floor($attackFrame->getPing() / 50))) < AntiCheat::getInstance()->getServer()->getTicksPerSecond()){
                    $hits++;
                }
            }

            if ($hits >= $this->CPS_LIMIT){
                if ($user->getViolation($this->getName()) < $this->getMaxViolations()){
                    $user->increaseViolation($this->getName(), 1);
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
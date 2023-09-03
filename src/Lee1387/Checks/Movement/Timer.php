<?php

namespace Lee1387\Checks\Movement;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\Checks\Notifier;
use Lee1387\AntiCheat;
use Lee1387\User\User;
use Lee1387\Utils\Constants;
use Lee1387\Utils\Raycast;

class Timer extends Check {

    public function __construct() {
        parent::__construct("Timer", 10);
    }

    public function onMove(Player $player, PlayerAuthInputPacket $packet, User $user): void {
        $player->sendMessage($user->getTickDelay());
        $newTickDelay = AntiCheat::getInstance()->getServer()->getTick() - $packet->getTick();
        $player->sendMessage($newTickDelay);
    }
}
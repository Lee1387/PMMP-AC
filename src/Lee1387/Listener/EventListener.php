<?php

namespace Lee1387\Listener;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\player\Player;
use Lee1387\Buffers\MovementFrame;
use Lee1387\Checks\Check;
use Lee1387\AntiCheat;
use Lee1387\User\User;

class EventListener implements Listener {

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     */
    public function onPacketReceive(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();

        if ($player == null || AntiCheat::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString()) == null) {
            return;
        }

        $uuid = $player->getUniqueId()->toString();
        $user = AntiCheat::getInstance()->getUserManager()->getUser($uuid);

        if ($packet instanceof PlayerAuthInputPacket) {
            $NewBuffer = new MovementFrame(
                $this->getServerTick(),
                $packet->getTick(),
                $packet->getPosition(),
                new Vector2($packet->getPitch(), $packet->getYaw()),
                $event->getOrigin()->getPlayer()->isOnGround(),
                $event->getOrigin()->getPlayer()->boundingBox
            );
            AntiCheat::getInstance()->getUserManager()->getUser($uuid)->addToMovementBuffer($NewBuffer);

            foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check) {
                $Check->onMove($packet, $user);
            }
        }
    }

    public function onAttack(EntityDamageByEntityEvent $event): void {
        $damager = $event->getDamager();

        if ($damager instanceof Player) {
            $user = AntiCheat::getInstance()->getUserManager()->getUser($damager->getUniqueId()->toString());
            foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check) {
                $Check->onAttack($event, $user);
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();
        $user = new User($uuid);

        AntiCheat::getInstance()->getUserManager()->registerUser($user);

        foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check) {
            $Check->onJoin($event, $user);
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();

        AntiCheat::getInstance()->getUserManager()->unregisterUser($uuid);
    }

    public function getServerTick(): int {
        return AntiCheat::getInstance()->getServer()->getTick();
    }
}
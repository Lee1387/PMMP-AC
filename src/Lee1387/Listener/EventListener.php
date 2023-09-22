<?php

namespace Lee1387\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\player\Player;
use Lee1387\Buffers\AttackFrame;
use Lee1387\Buffers\MovementFrame;
use Lee1387\Checks\Check;
use Lee1387\AntiCheat;
use Lee1387\User\User;
use Lee1387\Utils\Random;

class EventListener implements Listener
{

    /**
     * @param DataPacketReceiveEvent $event
     * @return void
     */
    public function onPacketReceive(DataPacketReceiveEvent $event): void
    {
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();

        if ($player == null || AntiCheat::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString()) == null){
            return;
        }

        $uuid = $player->getUniqueId()->toString();
        $user = AntiCheat::getInstance()->getUserManager()->getUser($uuid);

        if ($user == null){
            return;
        }

        if ($packet instanceof InventoryTransactionPacket){
            $data = $packet->trData;

            if ($data instanceof UseItemOnEntityTransactionData){
                $NewBuffer = new AttackFrame(
                    $this->getServerTick(),
                    $player->getNetworkSession()->getPing(),
                    $user->getLastAttack()
                );
                AntiCheat::getInstance()->getUserManager()->getUser($uuid)->addToAttackBuffer($NewBuffer);
            }
        }

        if ($packet instanceof PlayerAuthInputPacket){

            $moveForward = Random::clamp(-0.98, 0.98, $packet->getMoveVecX());
            $moveStrafe = Random::clamp(-0.98, 0.98, $packet->getMoveVecZ());

            $user->setMoveForward($moveForward);
            $user->setMoveStrafe($moveStrafe);

            foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
                $Check->onMove($player, $packet, $user);
            }

            $NewBuffer = new MovementFrame(
                $this->getServerTick(),
                $packet->getTick(),
                $packet->getPosition(),
                new Vector2($packet->getPitch(), $packet->getYaw()),
                $packet->getHeadYaw(),
                $event->getOrigin()->getPlayer()->isOnGround(),
                $event->getOrigin()->getPlayer()->boundingBox
            );
            $user->addToMovementBuffer($NewBuffer);

            if ($user->getFirstClientTick() == 0 && $user->getFirstServerTick() == 0){
                $user->setFirstServerTick($this->getServerTick());
                $user->setFirstClientTick($packet->getTick());
                $user->setTickDelay($this->getServerTick() - $packet->getTick());
            }

            if ($user->getInput() == 0){
                $user->setInput($packet->getInputMode());
            }

        }

    }

    public function onAttack(EntityDamageByEntityEvent $event): void
    {
        $damager = $event->getDamager();
        $victim = $event->getEntity();

        if ($victim instanceof Player){
            $victimUser = AntiCheat::getInstance()->getUserManager()->getUser($victim->getUniqueId()->toString());
            $victimUser->setLastKnockbackTick($this->getServerTick());
        }

        if ($damager instanceof Player){
            $user = AntiCheat::getInstance()->getUserManager()->getUser($damager->getUniqueId()->toString());
            foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
                $Check->onAttack($event, $user);
            }
            $user->setLastAttack($this->getServerTick());

            if ($user->isPunishNext()){
                $user->setPunishNext(false);
                $event->cancel();
            }
        }
    }

    public function onBlockBreak(BlockBreakEvent $event): void 
    {
        $player = $event->getPlayer();
        $user = AntiCheat::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());

        foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
            $Check->onBlockBreak($event, $user);
        }
    }

    public function onMotion(EntityMotionEvent $event){
        $entity = $event->getEntity();

        if ($entity instanceof Player) {
            $user = AntiCheat::getInstance()->getUserManager()->getUser($entity->getUniqueId()->toString());
            foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
                $Check->onMotion($event, $user);
            }
        }
    }

    /**
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event) : void
    {
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();
        $user = new User($uuid);

        AntiCheat::getInstance()->getUserManager()->registerUser($user);

        foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
            $Check->onJoin($event, $user);
        }
    }

    /**
     * @param PlayerQuitEvent $event
     * @return void
     */
    public function onQuit(PlayerQuitEvent $event) : void
    {
        $player = $event->getPlayer();
        $uuid = $player->getUniqueId()->toString();

        AntiCheat::getInstance()->getUserManager()->unregisterUser($uuid);
    }

    public function getServerTick() : int
    {
        return AntiCheat::getInstance()->getServer()->getTick();
    }

}
<?php

namespace Lee1387\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\inventory\UseItemOnEntityTransactionData;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\network\mcpe\protocol\types\PlayerMovementSettings;
use pocketmine\network\mcpe\protocol\types\PlayerMovementType;
use pocketmine\player\Player;
use Lee1387\Buffers\AttackFrame;
use Lee1387\Buffers\MovementFrame;
use Lee1387\AntiCheat;
use Lee1387\User\User;

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

        if ($player == null){
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
                $AttackFrame = new AttackFrame(
                    $this->getServerTick(),
                    $player->getNetworkSession()->getPing(),
                    $user->getLastAttack()
                );
                AntiCheat::getInstance()->getUserManager()->getUser($uuid)->addToAttackBuffer($AttackFrame);
            }
        }

        if ($packet instanceof PlayerAuthInputPacket){

            $user->preMove($packet, $player);

            foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
                $Check->onMove($packet, $user);
            }

            $user->postMove($packet, $player);

            $MoveFrame = new MovementFrame(
                $this->getServerTick(),
                $packet->getTick(),
                $packet->getPosition(),
                new Vector2($packet->getPitch(), $packet->getYaw()),
                $packet->getHeadYaw(),
                $player->isOnGround(),
                $player->boundingBox,
                $player->getDirectionVector()
            );
            $user->addToMovementBuffer($MoveFrame);

            if ($packet->hasFlag(PlayerAuthInputFlags::MISSED_SWING)){
                $AttackFrame = new AttackFrame(
                    $this->getServerTick(),
                    $player->getNetworkSession()->getPing(),
                    $user->getLastAttack()
                );
                AntiCheat::getInstance()->getUserManager()->getUser($uuid)->addToAttackBuffer($AttackFrame);
            }
            
        }

        if ($packet instanceof StartGamePacket){
            $packet->playerMovementSettings = new PlayerMovementSettings(PlayerMovementType::SERVER_AUTHORITATIVE_V2_REWIND, 40, false);
        }

    }

    /**
     * @param EntityDamageByEntityEvent $event
     * @return void
     */
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

            if ($user === null) {
                return;
            }
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

    /**
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreak(BlockBreakEvent $event): void 
    {
        $player = $event->getPlayer();
        $user = AntiCheat::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());

        if ($user === null) {
            return;
        }

        foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
            $Check->onBlockBreak($event, $user);
        }
    }

    /**
     * @param EntityMotionEvent $event
     * @return void
     */
    public function onMotion(EntityMotionEvent $event): void
    {
        $entity = $event->getEntity();

        if ($entity instanceof Player) {
            $user = AntiCheat::getInstance()->getUserManager()->getUser($entity->getUniqueId()->toString());

            if ($user === null) {
                return;
            }
            
            foreach (AntiCheat::getInstance()->getCheckManager()->getChecks() as $Check){
                if ($user != null){
                    $Check->onMotion($event, $user);
                }
            }
            $user->getMotion()->addVector($event->getVector());
            $user->resetTicksSinceMotion();
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
        $user = new User($player, $uuid);

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

    /**
     * @return int
     */
    public function getServerTick() : int
    {
        return AntiCheat::getInstance()->getServer()->getTick();
    }

}
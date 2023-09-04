<?php

namespace Lee1387\Checks;

use pocketmine\math\Vector3;
use pocketmine\permission\BanEntry;
use pocketmine\player\Player;
use Lee1387\AntiCheat;
use Lee1387\User\User;
use Lee1387\Utils\Constants;

class Punishments
{

    /**
     * @param Player $player
     * @param Vector3|null $position
     * @param string|null $punishment
     * @return void
     */
    public static function punishPlayer(Player $player, Check $check, User $user, ?Vector3 $position, ?string $punishment): void
    {
        if ($punishment == null){
            return;
        }

        switch ($punishment){
            case "Cancel":
                if ($position != null){
                    $player->teleport($position);
                    $user->resetViolation($check->getName());
                }
                break;
            case "Kick":
                self::KickUser($player);
                break;
            case "Ban":
                self::BanUser($player);
                break;
        }
    }

    public static function KickUser(Player $player): void
    {
        $config = AntiCheat::getInstance()->getConfig();
        $message = $config->get("kick-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);

        $player->kick($message);
    }

    public static function BanUser(Player $player): void
    {

        $config = AntiCheat::getInstance()->getConfig();
        $message = $config->get("ban-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);

        $Ban = new BanEntry($player->getName());
        $Ban->setReason($message);
        AntiCheat::getInstance()->getServer()->getNameBans()->add($Ban);
        $player->kick($message);
    }

}
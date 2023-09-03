<?php

namespace Lee1387\Checks;

use Lee1387\AntiCheat;
use Lee1387\Utils\Constants;

class Notifier {

    /**
     * @param string $name
     * @param string $Check
     * @param int $Violation
     * @return void
     */
    public static function NotifyFlag(string $name, string $Check, int $Violation, bool $notify): void {
        if (!AntiCheat::getInstance()->getConfig()->get("enable-debug") || !$notify) {
            self::NotifyPlayers($name, $Check, $Violation);
            return;
        }
        
        foreach (AntiCheat::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage(Constants::PREFIX . $name . "§f failed §a" . $Check . " §a[§4" . $Violation . "§a]");
        }
    }

    public static function NotifyPlayers(string $name, string $Check, int $Violation): void {
        foreach (AntiCheat::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if ($player->hasPermission("anticheat.notify")) {
                $player->sendMessage(Constants::PREFIX . $name . "§f is using §a" . $Check);
            }
        }
    }
}
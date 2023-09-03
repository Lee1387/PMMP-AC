<?php

namespace Lee1387\Checks;

use Lee1387\AntiCheat;

class Notifier {

    /**
     * @param string $name
     * @param string $Check
     * @param int $Violation
     * @return void
     */
    public static function NotifyFlag(string $name, string $Check, int $Violation): void {
        foreach (AntiCheat::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage("§6[AntiCheat] §a" . $name . "§f failed §a" . $Check . " §a[§4" . $Violation . "§a]");
        }
    }
}
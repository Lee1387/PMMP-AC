<?php

namespace Lee1387\Checks;

use Lee1387\AntiCheat;
use Lee1387\Utils\Constants;

class Notifier {

    /**
     * @param string $name
     * @param string $Check
     * @param int $Violation
     * @param bool $notify
     * @return void
     */
    public static function NotifyFlag(string $name, string $Check, int $Violation, bool $notify): void {
        if (!AntiCheat::getInstance()->getConfig()->get("enable-debug") || !$notify) {
            if ($notify) {
                self::NotifyPlayers($name, $Check);
            }
            return;
        }

        $config = AntiCheat::getInstance()->getConfig();
        $message = $config->get("alert-message-debug");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos, 8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check, $msgCheckPos, 7);
        $msgViolationPos = strpos($message, "%VIOLATION%");
        $message = substr_replace($message, $Violation, $msgViolationPos, 11);
        
        foreach (AntiCheat::getInstance()->getServer()->getOnlinePlayers() as $player) {
            $player->sendMessage($message);
        }
    }

    /**
     * @param string $name
     * @param string $Check
     * @return void
     */
    public static function NotifyPlayers(string $name, string $Check): void {

        $config = AntiCheat::getInstance()->getConfig();
        $message = $config->get("alert-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos,8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check, $msgCheckPos, 7);

        foreach (AntiCheat::getInstance()->getServer()->getOnlinePlayers() as $player) {
            if ($player->hasPermission("anticheat.notify")) {
                $player->sendMessage($message);
            }
        }
    }
}
<?php

namespace Lee1387\Checks;

use Lee1387\AntiCheat;
use Lee1387\User\User;

class Notifier
{

    /**
     * @param string $name
     * @param User $user
     * @param Check $Check
     * @param float $Violation
     * @param bool $notify
     * @return void
     */
    public static function NotifyFlag(string $name, User $user, Check $Check, float $Violation, bool $notify) : void
    {
        $config = AntiCheat::getInstance()->getSavedConfig();
        $user->increaseAlertCount($Check->getName());

        if ($user->getAlertCount($Check->getName()) < $Check->getAlertFrequency()){
            return;
        }

        if (!AntiCheat::getInstance()->getSavedConfig()->get("enable-debug")){
            if ($notify){
                self::NotifyPlayers($name, $user, $Check);
            }
            return;
        }

        $message = $config->get("alert-message-debug");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos,8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check->getName(), $msgCheckPos, 7);
        $msgViolationPos = strpos($message, "%VIOLATION%");
        $message = substr_replace($message, $Violation, $msgViolationPos, 11);

        foreach (AntiCheat::getInstance()->getServer()->getOnlinePlayers() as $player){

            $notifyUser = AntiCheat::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());
            $hasNotifications = $notifyUser->hasNotifications();

            if ($hasNotifications){
                $player->sendMessage($message);
            }
        }
        $user->resetAlertCount($Check->getName());
    }

    /**
     * @param string $name
     * @param User $user
     * @param Check $Check
     * @return void
     */
    public static function NotifyPlayers(string $name, User $user, Check $Check) : void
    {

        $config = AntiCheat::getInstance()->getSavedConfig();
        $message = $config->get("alert-message");
        $prefix = $config->get("prefix");

        $msgPrefixPos = strpos($message, "%PREFIX%");
        $message = substr_replace($message, $prefix, $msgPrefixPos, 8);
        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $name, $msgPlayerPos,8);
        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $Check->getName(), $msgCheckPos, 7);

        foreach (AntiCheat::getInstance()->getServer()->getOnlinePlayers() as $player){

            $notifyUser = AntiCheat::getInstance()->getUserManager()->getUser($player->getUniqueId()->toString());
            $hasNotifications = $notifyUser->hasNotifications();

            if ($player->hasPermission("anticheat.notify")){
                if ($hasNotifications){
                    $player->sendMessage($message);
                }
            }
        }
        $user->resetAlertCount($Check->getName());
    }

}
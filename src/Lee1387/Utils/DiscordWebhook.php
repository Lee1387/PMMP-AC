<?php

namespace Lee1387\Utils;

use pocketmine\player\Player;
use Lee1387\Checks\Check;
use Lee1387\AntiCheat;

class DiscordWebhook 
{

    public static function TestNotification(): void 
    {
        if (!AntiCheat::getInstance()->WebhookEnabled()) {
            AntiCheat::getInstance()->getLogger()->warning("Webhook Disabled");
            return;
        }

        $data = array('content' => 'Webhook TEST');
        $data = json_encode($data);

        $curl = curl_init(self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_URL, self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_exec($curl);

        if (curl_error($curl)) {
            AntiCheat::getInstance()->getLogger()->warning(curl_error($curl));
        }
    }

    public static function PostNotification(Player $player, Check $check): void 
    {
        if (!AntiCheat::getInstance()->WebhookEnabled()) {
            return;
        }

        $message = self::BuildMessage($player, $check);
        $data = array('content' => $message);
        $data = json_encode($data);

        $curl = curl_init(self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_URL, self::GetWebhookURL());
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

        curl_exec($curl);

        if (curl_error($curl)) {
            AntiCheat::getInstance()->getLogger()->warning(curl_error($curl));
        }
    }

    private static function BuildMessage(Player $player, Check $check): string 
    {
        $config = AntiCheat::getInstance()->getSavedConfig();
        $message = $config->get("webhook-message");

        $msgPlayerPos = strpos($message, "%PLAYER%");
        $message = substr_replace($message, $player->getName(), $msgPlayerPos, 8);

        $msgCheckPos = strpos($message, "%CHECK%");
        $message = substr_replace($message, $check->getName(), $msgCheckPos, 7);

        return $message;
    }

    private static function GetWebhookURL(): string 
    {
        $config = AntiCheat::getInstance()->getSavedConfig();
        return $config->get("webhook-url");
    }

}
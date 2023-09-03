<?php

namespace Lee1387;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use Lee1387\Checks\CheckManager;
use Lee1387\Listener\EventListener;
use Lee1387\User\UserManager;

class AntiCheat extends PluginBase implements \pocketmine\event\Listener {

    private static AntiCheat $instance;

    public UserManager $userManager;
    public CheckManager $checkManager;

    public function onEnable(): void {
        self::$instance = $this;

        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->getResource("config.yml");

        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

        $this->userManager = new UserManager();
        $this->checkManager = new CheckManager();
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if ($command->getName() == "test") {
            if (isset($args[0])) {
                $sender->sendMessage($args[0]);
            }
        }
        return false;
    }

    /**
     * @return AntiCheat
     */
    public static function getInstance(): AntiCheat {
        return self::$instance;
    }

    /**
     * @return CheckManager
     */
    public function getCheckManager(): CheckManager {
        return $this->checkManager;
    }

    /**
     * @return UserManager
     */
    public function getUserManager(): UserManager {
        return $this->userManager;
    }
}
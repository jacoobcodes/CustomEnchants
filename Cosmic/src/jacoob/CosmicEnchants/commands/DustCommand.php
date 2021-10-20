<?php

namespace jacoob\CosmicEnchants\commands;

use jacoob\CosmicEnchants\Maine;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat as C;

class DustCommand extends PluginCommand
{
    /**
     * @var Main
     */
    private $plugin;

    /**
     * ItemsCommand constructor.
     * @param Main $plugin
     */
    public function __construct(Maine $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("dust", $plugin);
        $this->setPermission("cosmic.dust");
        $this->setUsage("/dust <percentage> <player>");
        $this->setDescription("§bAdmin Command.");
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if(!$this->testPermission($sender)) return;
        if (count($args) !== 2) {
            $sender->sendMessage(C::RED . $this->getUsage());
            return;
        }
        if (intval($args[0]) > 100) {
            $sender->sendMessage(C::RED . $this->getUsage());
            return;
        }
        $player = $this->plugin->getServer()->getPlayer($args[1]);
        if ($player === null) {
            $sender->sendMessage(C::RED . "Player is offline!");
            return;
        }
        $percentage = (int)$args[0];
        $dust = $this->plugin->enchantFactory->getEnchantDust();
        $player->getInventory()->addItem($dust);
    }
}
<?php

namespace jacoob\CosmicEnchants\commands;

use jacoob\CosmicEnchants\Maine;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat as C;

class ScrollCommand extends PluginCommand
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
        parent::__construct("scroll", $plugin);
        $this->setPermission("cosmic.scroll");
        $this->setUsage("/scroll <black/armor/white/weapon/transmog> <player> <percentage/tier>");
            $this->setDescription("§bAdmin Command.");
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if(!$this->testPermission($sender)) return;
        if (count($args) < 2) {
            $sender->sendMessage(C::RED . $this->getUsage());
            return;
        }
        if(isset($args[0])) {
            switch ($args[0]) {
                case "black":
                    $item = $this->plugin->enchantFactory->getBlackScroll(isset($args[2]) ? (int) $args[2] : mt_rand(50, 100));
                    break;
                case "white":
                    $item = $this->plugin->enchantFactory->getWhiteScroll();
                    break;
                case "armor":
                    $item = $this->plugin->enchantFactory->getArmorOrb(isset($args[2]) ? (int) $args[2] : mt_rand(10, 15));
                    break;
                case "weapon":
                    $item = $this->plugin->enchantFactory->getWeaponOrb(isset($args[2]) ? (int) $args[2] : mt_rand(10, 15));
                    break;
                case "transmog":
                    $item = $this->plugin->enchantFactory->getTransmogScroll();
                    break;
                default:
                    $sender->sendMessage(C::RED . $this->getUsage());
                    return;
            }
        } else {
            $sender->sendMessage(C::RED . $this->getUsage());
            return;
        }
        if(isset($args[1])) {
            $player = $this->plugin->getServer()->getPlayer($args[1]);
            if ($player === null) {
                $sender->sendMessage(C::RED . "Player is offline!");
                return;
            }
            $player->getInventory()->addItem($item);
        }
    }
}
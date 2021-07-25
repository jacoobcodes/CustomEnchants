<?php

namespace Heisenburger69\CosmicEnchants\commands;

use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use Heisenburger69\CosmicEnchants\libs\jojoe77777\FormAPI\SimpleForm;
use Heisenburger69\CosmicEnchants\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class GiveXpCommand extends PluginCommand
{
    /**
     * @var Main
     */
    private $plugin;

    /**
     * ItemsCommand constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("givexp", $plugin);
        $this->setUsage("/givexp <player> <amount>");
        $this->setPermission("givexp.command");
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if(!$this->testPermission($sender)) return;
        if(count($args) !== 2) {
            $sender->sendMessage(C::RED . $this->getUsage());
            return;
        }
        $playerName = $args[0];
        if(($player = $this->plugin->getServer()->getPlayerExact($playerName)) === null) {
            $sender->sendMessage(C::RED . "Player not online!");
            return;
        }
        $xp = (int)$args[1];
        $player->addXp((int)$xp);
        $sender->sendMessage(C::GREEN . "Successfully added " . C::AQUA . $xp . C::GREEN . " XP to " .C::AQUA. $playerName);
    }
}
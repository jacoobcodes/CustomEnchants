<?php

namespace jacoob\CosmicEnchants\commands;

use jacoob\CosmicEnchants\Maine;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\IntTag;
use pocketmine\item;
use pocketmine\item\ItemIds;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat as C;

class GiveBookCommand extends PluginCommand
{
    /**
     * @var Main
     */
    private $plugin;

    /**
     * ItemsCommand constructor.
     * @param Main $plugin
     */
    public static $ces = ["simple","unique","elite","ultimate","legendary"];

    public function __construct(Maine $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("cebook", $plugin);
        $this->setPermission("cosmic.cebook");
        $this->setUsage("/cebook <type> <amount> <player>");
        $this->setDescription("§bAdmin Command.");
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if (!$sender instanceof Player) return;
        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage("");
            return;
        }
        if(!isset($args[0])){
            $sender->sendMessage("You must type /cebook give <type> <amount> <player>");
            return;
        }
        if(!isset($args[3])){
            $sender->sendMessage("Invalid Syntax.");
            return;
        }
        if(isset($args[0])){
            switch ($args[0]){
                case "give":
                    if(count($args) < 3){
                        $sender->sendMessage("Usage: /cebook give <type> <amount> <player>");
                    }elseif (in_array($args[1], self::$ces) && is_numeric($args[2]) && $player = Server::getInstance()->getPlayer($args[3])){
                        $player = Server::getInstance()->getPlayer($args[3]);
                        $player->sendMessage("You have been given " . $args[2] . " books.");
                        $this->giveCeBook($player, $args[1], $args[2]);  //$args1 is type
                    }else{
                        $sender->sendMessage("You must do /cebook give <type> <amount> <player>");
                        return;
                    }
                    break;
            }
        }
    }

    public function giveCeBook(Player $player, string $ce, int $amount): void{
        switch ($ce){
            case "simple":
                $item = \pocketmine\item\Item::get(340, 0,$amount);
                $item->setNamedTagEntry(new IntTag("randomcebook", 1));
                $item->setCustomName(C::YELLOW . C::BOLD . "Random Simple Enchantment");
                $item->setLore([C::RESET . C::GRAY . "Tap to receive a random Simple Enchantment."]);
                $player->getInventory()->addItem($item);
                break;
            case "unique":
                $item = \pocketmine\item\Item::get(340, 0,$amount);
                $item->setNamedTagEntry(new IntTag("randomcebook", 2));
                $item->setCustomName(C::BLUE . C::BOLD . "Random Unique Enchantment");
                $item->setLore([C::RESET . C::GRAY . "Tap to receive a random Unique Enchantment."]);
                $player->getInventory()->addItem($item);
                break;
            case "elite":
                $item = \pocketmine\item\Item::get(340, 0,$amount);
                $item->setNamedTagEntry(new IntTag("randomcebook", 3));
                $item->setCustomName(C::DARK_PURPLE . C::BOLD . "Random Elite Enchantment");
                $item->setLore([C::RESET . C::GRAY . "Tap to receive a random Elite Enchantment."]);
                $player->getInventory()->addItem($item);
                break;
            case "ultimate":
                $item = \pocketmine\item\Item::get(340, 0,$amount);
                $item->setNamedTagEntry(new IntTag("randomcebook", 4));
                $item->setCustomName(C::LIGHT_PURPLE . C::BOLD . "Random Ultimate Enchantment");
                $item->setLore([C::RESET . C::GRAY . "Tap to receive a random Ultimate Enchantment."]);
                $player->getInventory()->addItem($item);
                break;
            case "legendary":
                $item = \pocketmine\item\Item::get(340, 0,$amount);
                $item->setNamedTagEntry(new IntTag("randomcebook", 5));
                $item->setCustomName(C::LIGHT_PURPLE . C::BOLD . "Random Legendary Enchantment");
                $item->setLore([C::RESET . C::GRAY . "Tap to receive a random Legendary Enchantment."]);
                $player->getInventory()->addItem($item);
                break;
        }
    }
}
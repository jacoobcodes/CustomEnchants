<?php

namespace jacoob\CosmicEnchants\commands;

use jacoob\CosmicEnchants\libs\muqsit\invmenu\inventories\BaseFakeInventory;
use jacoob\CosmicEnchants\libs\muqsit\invmenu\InvMenu;
use jacoob\CosmicEnchants\Maine;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class TinkerCommand extends PluginCommand
{
    /**
     * @var Main
     */
    private $plugin;

    /**
     * ItemsCommand constructor.
     * @param Maine $plugin
     */
    public function __construct(Maine $plugin)
    {
        $this->plugin = $plugin;
        parent::__construct("tinker", $plugin);
        $this->setUsage("/tinker");
        $this->setDescription("Tinker items with enchantments in exchange for enchant dust!");
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::RED . "This can only be used in-game!");
            return;
        }
        $this->sendTinkerMenu($sender);
    }

    private function sendTinkerMenu(Player $player): void
    {
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $menu->setName(C::BOLD . C::DARK_AQUA . "Tinkerer");
        $menu->setListener([$this, "onTinker"]);
        $menu->setInventoryCloseListener([$this, "onClose"]);
        $inv = $menu->getInventory();

        $item = Item::get(Item::STAINED_GLASS_PANE, 4, 1);
        $item->setCustomName(C::RESET . C::BOLD . C::YELLOW . "Deposit All");
        $inv->setItem(52, $item);

        $item = Item::get(Item::STAINED_GLASS_PANE, 5, 1);
        $item->setCustomName(C::RESET . C::BOLD . C::GREEN . "Tinker");
        $inv->setItem(53, $item);

        $menu->send($player);
    }

    public function onTinker(Player $player, Item $itemClicked, Item $itemClickedWith, SlotChangeAction $action): bool
    {
        $slot = $action->getSlot();
        if($slot === 53) {
            $action->getInventory()->close($player);
            return false;
        }
        if($slot === 52) {
            foreach ($player->getInventory()->getContents() as $item) {
                if($item->hasEnchantments()) {
                    $player->getInventory()->removeItem($item);
                    $action->getInventory()->addItem($item);
                }
            }
            return false;
        }
        return true;
    }

    public function onClose(Player $player, BaseFakeInventory $inventory){
        foreach ($inventory->getContents() as $item) {
            $count = count($item->getEnchantments());
            if($count === 0) continue;
            $multiplier = $this->plugin->getConfig()->get("tinkerer-per-enchant");
            if($multiplier === false) $multiplier = 1;
            $dust = $this->plugin->enchantFactory->getEnchantDust($multiplier * $count);
            $player->getInventory()->addItem($dust);
        }
        $player->sendMessage(C::GREEN . "Successfully tinkered items!");
    }
}
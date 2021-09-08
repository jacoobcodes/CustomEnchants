<?php

namespace jacoob\CosmicEnchants\commands;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use jacoob\CosmicEnchants\Maine;
use jacoob\CosmicEnchants\libs\jojoe77777\FormAPI\CustomForm;
use jacoob\CosmicEnchants\libs\jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class CEShopCommand extends PluginCommand
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
        parent::__construct("ceshop", $plugin);
        $this->setUsage("/ceshop");
    }

    public function execute(CommandSender $sender, string $label, array $args)
    {
        if ($sender instanceof Player) {
            $form = new SimpleForm(function ($sender, $data) {
                if (!is_null($data)) $this->confirm($sender, $data);
            });
            $form->setTitle("Custom Enchants Shop");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_SIMPLE) . C::BOLD . "Simple");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_UNIQUE) . C::BOLD . "Unique");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_ELITE) . C::BOLD . "Elite");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_ULTIMATE) . C::BOLD . "Ultimate");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_LEGENDARY) . C::BOLD . "Legendary");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_SOUL) . C::BOLD . "Soul");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_HEROIC) . C::BOLD . "Heroic");
            $form->addButton(Utils::getColorFromRarity(CustomEnchant::RARITY_MASTERY) . C::BOLD . "Mastery");
            $sender->sendForm($form);
            return;
        }
    }

    public function confirm(Player $player, int $dataid): void
    {
        $rarity = $this->dataIdToRarity($dataid);
        if ($rarity === null) {
            return;
        }
        $cost = $this->getCost($rarity);
        $form = new CustomForm(function (Player $player, $data) use ($rarity, $cost) {
            if ($data !== null) {
                $amount = $data[2];
                $xpPrice = $cost * $amount;
                if($player->getCurrentTotalXp() >= $xpPrice) {
                    $item = $this->plugin->enchantFactory->getRarityBook($rarity, $amount);
                    if($player->getInventory()->canAddItem($item)) {
                        $player->getInventory()->addItem($item);
                        $player->subtractXp($xpPrice);
                    } else {
                        $player->sendMessage(C::RED . "You do not have enough Inventory Space to buy " . C::AQUA . $amount . C::RED . " books");
                        return;
                    }
                    $player->sendMessage(C::GREEN . "Successfully purchased " . C::AQUA . $amount . C::GREEN . " books for " . C::AQUA . $xpPrice . "XP");
                } else {
                    $player->sendMessage(C::RED . "You do not have enough XP to buy $amount Books.\n" . C::AQUA . "Required XP: " . C::GREEN . $xpPrice);
                }
            }
        });
        $form->setTitle(Utils::getColorFromRarity($rarity) . C::BOLD . $this->plugin->rarityToName($rarity));
        $form->addLabel(C::GREEN . "How many books do you want to purchase?");
        $form->addLabel(C::AQUA . "Cost Per Book: $cost XP");
        $form->addSlider(C::GREEN . "Amount", 1, 64, 1);
        $player->sendForm($form);
    }

    public function dataIdToRarity(int $dataid): ?int
    {
        switch ($dataid) {
            case 0:
                return 1;
            case 1:
                return 2;
            case 2:
                return 3;
            case 3:
                return 4;
            case 4:
                return 5;
            case 5:
                return 6;
            case 6:
                return 7;
            case 7:
                return 10;
        }
        return null;
    }

    public function getCost(int $rarity): ?int
    {   
        switch ($rarity) {
            case 1:
                return $this->plugin->getConfig()->get("simple");
            case 2:
                return $this->plugin->getConfig()->get("unique");
            case 3:
                return $this->plugin->getConfig()->get("elite");
            case 4:
                return $this->plugin->getConfig()->get("ultimate");
            case 5:
                return $this->plugin->getConfig()->get("legendary");
            case 6:
                return $this->plugin->getConfig()->get("soul");
            case 7:
                return $this->plugin->getConfig()->get("heroic");
            case 10:
                return $this->plugin->getConfig()->get("mastery");
        }
        return null;
    }
}
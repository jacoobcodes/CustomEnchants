<?php

namespace jacoob\CosmicEnchants;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;

class EnchantFactory
{
    /**
     * @var Maine
     */
    private $plugin;

    /**
     * BookFactory constructor.
     * @param Main $plugin
     */
    public function __construct(Maine $plugin)
    {
        $this->plugin = $plugin;
    }

    public function getCEEnchantBook(int $enchantId, int $level = 1, int $success = 100, int $destroy = 50): ?Item
    {
        $book = Item::get(340);
        $enchant = CustomEnchantManager::getEnchantment($enchantId);
        if ($enchant !== null) {
            $enchInstance = new EnchantmentInstance($enchant, $level);
            $book->setCustomName(C::RESET . C::BOLD . Utils::getColorFromRarity($enchant->getRarity()) . $enchant->getName() . " " . ($this->plugin->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants")->getConfig()->getNested("enchants.roman-numerals") ? Utils::getRomanNumeral($enchInstance->getLevel()) : $enchInstance->getLevel()));

            $description = $enchant->getDescription();
            $pos = strpos($description, " ", strlen($description) > 35 ? 35 : strlen($description));
            if($pos !== false) $description = chunk_split($description, $pos, "\n");
            $book->setLore([
                C::RESET . C::GREEN . "$success% Success Rate",
                C::RESET . C::RED . "$destroy% Destroy Rate",
                C::RESET . C::YELLOW . $description,
                C::RESET . C::GRAY . "Drag n' Drop onto item to enchant."
            ]);
        }
        $book->setNamedTagEntry(new IntTag("enchantbook", $enchantId));
        $book->setNamedTagEntry(new IntTag("levelbook", $level));
        $book->setNamedTagEntry(new IntTag("successbook", $success));
        $book->setNamedTagEntry(new IntTag("destroybook", $destroy));
        return $book;
    }

    public function giveCEBook(Player $player)
    {
        $item = $this->plugin->enchantFactory->getRarityBook("Soul", "5");
        if ($player->getInventory()->canAddItem($item)) {
            $player->getInventory()->addItem($item);
        }
    }

    public function getRarityBook(int $rarity, int $amount): Item
    {
        $item = Item::get(340, 0, $amount);
        $item->setCustomName(C::RESET . C::BOLD . Utils::getColorFromRarity($rarity) . $this->plugin->rarityToName($rarity) . C::WHITE . " Book");
        $item->setNamedTagEntry(new IntTag("randomcebook", $rarity));
        $item->setLore([C::RESET . C::GRAY . "Tap this item to redeem a random Custom Enchant"]);
        return $item;
    }

    /**
     * @return Item
     */
    public function getTransmogScroll(): Item
    {
        $item = Item::get($this->plugin->getConfig()->getNested("transmogscroll.id"));
        $item->setCustomName(C::RESET . C::colorize($this->plugin->getConfig()->getNested("transmogscroll.name")));

        $lore = $this->plugin->getConfig()->getNested("transmogscroll.lore");
        $itemLore = [];
        foreach ($lore as $line) {
            $itemLore[] = C::RESET . C::colorize($line);
        }
        $item->setLore($itemLore);

        $item->setNamedTagEntry(new IntTag("transmogscroll", mt_rand(0, 100000)));
        if ($this->plugin->getConfig()->getNested("transmogscroll.glint")) $item->setNamedTagEntry(new ListTag("ench"));

        return $item;
    }

    /**
     * @return Item
     */
    public function getWhiteScroll(): Item
    {
        $item = Item::get($this->plugin->getConfig()->getNested("whitescroll.id"));
        $item->setCustomName(C::RESET . C::colorize($this->plugin->getConfig()->getNested("whitescroll.name")));

        $lore = $this->plugin->getConfig()->getNested("whitescroll.lore");
        $itemLore = [];
        foreach ($lore as $line) {
            $itemLore[] = C::RESET . C::colorize($line);
        }
        $item->setLore($itemLore);

        $item->setNamedTagEntry(new IntTag("whitescroll", mt_rand(0, 100000)));
        if ($this->plugin->getConfig()->getNested("whitescroll.glint")) $item->setNamedTagEntry(new ListTag("ench"));

        return $item;
    }

    /**
     * @param int $percent
     * @return Item
     */
    public function getBlackScroll($percent = 100): Item
    {
        $item = Item::get($this->plugin->getConfig()->getNested("blackscroll.id"));
        $item->setCustomName(C::RESET . C::colorize($this->plugin->getConfig()->getNested("blackscroll.name")));

        $lore = $this->plugin->getConfig()->getNested("blackscroll.lore");
        $itemLore = [];
        foreach ($lore as $line) {
            $line = str_replace("{SUCCESS}", $percent, $line);
            $itemLore[] = C::RESET . C::colorize($line);
        }
        $item->setLore($itemLore);

        $item->setNamedTagEntry(new IntTag("blackscroll", $percent));
        if ($this->plugin->getConfig()->getNested("blackscroll.glint")) $item->setNamedTagEntry(new ListTag("ench"));

        return $item;
    }

    /**
     * @param int $percent
     * @return Item
     */
    public function getEnchantDust($percent = 100): Item
    {
        $item = Item::get($this->plugin->getConfig()->getNested("enchantdust.id"));
        $item->setCustomName(C::RESET . C::colorize($this->plugin->getConfig()->getNested("enchantdust.name")));

        $lore = $this->plugin->getConfig()->getNested("enchantdust.lore");
        $itemLore = [];
        foreach ($lore as $line) {
            $line = str_replace("{PERCENT}", $percent, $line);
            $itemLore[] = C::RESET . C::colorize($line);
        }
        $item->setLore($itemLore);

        $item->setNamedTagEntry(new IntTag("enchantdust", $percent));
        if ($this->plugin->getConfig()->getNested("enchantdust.glint")) $item->setNamedTagEntry(new ListTag("ench"));

        return $item;
    }

    /**
     * @param int $tier
     * @return Item
     */
    public function getWeaponOrb($tier = 10): Item
    {
        $item = Item::get($this->plugin->getConfig()->getNested("weaponorb.id"));
        $item->setCustomName(C::RESET . C::colorize($this->plugin->getConfig()->getNested("weaponorb.name")));

        $lore = $this->plugin->getConfig()->getNested("weaponorb.lore");
        $itemLore = [];
        foreach ($lore as $line) {
            $line = str_replace("{TIER}", $tier, $line);
            $itemLore[] = C::RESET . C::colorize($line);
        }
        $item->setLore($itemLore);

        $item->setNamedTagEntry(new IntTag("weaponorb", $tier));
        if ($this->plugin->getConfig()->getNested("weaponorb.glint")) $item->setNamedTagEntry(new ListTag("ench"));

        return $item;
    }

    /**
     * @param int $tier
     * @return Item
     */
    public function getArmorOrb($tier = 10): Item
    {
        $item = Item::get($this->plugin->getConfig()->getNested("armororb.id"));
        $item->setCustomName(C::RESET . C::colorize($this->plugin->getConfig()->getNested("armororb.name")));

        $lore = $this->plugin->getConfig()->getNested("armororb.lore");
        $itemLore = [];
        foreach ($lore as $line) {
            $line = str_replace("{TIER}", $tier, $line);
            $itemLore[] = C::RESET . C::colorize($line);
        }
        $item->setLore($itemLore);

        $item->setNamedTagEntry(new IntTag("armororb", $tier));
        if ($this->plugin->getConfig()->getNested("armororb.glint")) $item->setNamedTagEntry(new ListTag("ench"));

        return $item;
    }


}
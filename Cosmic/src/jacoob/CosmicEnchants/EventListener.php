<?php

namespace jacoob\CosmicEnchants;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\utils\Utils;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\TextFormat as C;

class EventListener implements Listener
{
    /**
     * @var Main
     */
    private $plugin;

    /**
     * EventListener constructor.
     * @param Main $plugin
     */
    public function __construct(Maine $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerInteractEvent $event
     */
    public function onTapBook(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $nbt = $item->getNamedTagEntry("randomcebook");
        if ($nbt !== null) {
            $rarity = $nbt->getValue();
            $enchants = [];
            switch ($rarity) {
                case 1:
                    $enchants = $this->plugin->getSimpleEnchants();
                    break;
                case 2:
                    $enchants = $this->plugin->getUniqueEnchants();
                    break;
                case 3:
                    $enchants = $this->plugin->getEliteEnchants();
                    break;
                case 4:
                    $enchants = $this->plugin->getUltimateEnchants();
                    break;
                case 5:
                    $enchants = $this->plugin->getLegendaryEnchants();
                    break;
                case 6:
                    $enchants = $this->plugin->getSoulEnchants();
                    break;
                case 7:
                    $enchants = $this->plugin->getHeroicEnchants();
                    break;
                case 8:
                    $enchants = $this->plugin->getMasteryEnchants();
                    break;
            }
            if (!empty($enchants)) {
                $enchant = $enchants[array_rand($enchants)];
                $ce = CustomEnchantManager::getEnchantment($enchant);
                if ($ce instanceof CustomEnchant) {
                    $item->pop();
                    $player->getInventory()->setItemInHand($item);
                    $level = mt_rand(1, $ce->getMaxLevel());
                    $book = $this->plugin->enchantFactory->getCEEnchantBook($enchant, $level, mt_rand(1, 100), mt_rand(0, 100));
                    $player->getInventory()->addItem($book);
                }
            }
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     * @priority HIGH
     * @ignoreCancelled true
     */
    public function onApplyBook(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $oldToNew = isset(array_keys($actions)[0]) ? $actions[array_keys($actions)[0]] : null;
        $newToOld = isset(array_keys($actions)[1]) ? $actions[array_keys($actions)[1]] : null;
        if ($oldToNew instanceof SlotChangeAction && $newToOld instanceof SlotChangeAction) {
            $itemClicked = $newToOld->getSourceItem();
            $itemClickedWith = $oldToNew->getSourceItem();
            if ($itemClickedWith->getId() === 340 && $itemClicked->getId() !== Item::AIR) {
                $nbt = $itemClickedWith->getNamedTagEntry("enchantbook");
                if ($nbt === null) return;
                $enchantment = $nbt->getValue();

                $enchantment = CustomEnchantManager::getEnchantment($enchantment);
                $success = $itemClickedWith->getNamedTagEntry("successbook")->getValue();
                $level = $itemClickedWith->getNamedTagEntry("levelbook")->getValue();
                $destroy = $itemClickedWith->getNamedTagEntry("destroybook")->getValue();

                $customEnch = [];
                foreach ($itemClicked->getEnchantments() as $enchantmentItem) {
                    if ($enchantmentItem->getType() instanceof CustomEnchant) {
                        $customEnch[] = $enchantmentItem;
                    }
                }

                $currentCe = count($customEnch);
                $limit = (int)$this->plugin->getConfig()->get("max-enchants");
                if (($orb = $itemClicked->getNamedTagEntry("orb")) !== null) {
                    $limit = $orb->getValue();
                }
                if ($currentCe >= $limit) {
                    $event->getTransaction()->getSource()->sendMessage(C::RED . "The max number of enchantments you can apply to this item is " . C::AQUA . $limit);
                    return;
                }

                if ($itemClicked->getNamedTagEntry("successbook") !== null) {
                    return;
                }
                if (!Utils::canBeEnchanted($itemClicked, $enchantment, $level)) {
                    $event->getTransaction()->getSource()->sendMessage(C::RED . "This item is not compatible with this enchant.");
                    return;
                }

                if (mt_rand(0, 100) > $success) {
                    if (mt_rand(0, 100) < $destroy) {
                        if ($itemClicked->getNamedTagEntry("protected") !== null) {
                            $event->setCancelled();
                            $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                            $event->getTransaction()->getSource()->sendMessage(C::RED . "Enchanting failed. The book has been destroyed and the protection on the item has been removed.");

                            $itemClicked->removeNamedTagEntry("protected");
                            $oldLore = $itemClicked->getLore();
                            $newLore = [];
                            foreach ($oldLore as $line) {
                                if (strpos($line, "PROTECTED") !== false) {
                                    $newLore[] = $line;
                                }
                            }
                            $itemClicked->setLore($newLore);
                            $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);
                            return;
                        }
                        $event->getTransaction()->getSource()->sendMessage(C::RED . "Enchanting failed. The book and the item have both been destroyed.");
                        $event->setCancelled();
                        $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                        $newToOld->getInventory()->setItem($newToOld->getSlot(), Item::get(Item::AIR));
                        return;
                    }
                    $event->getTransaction()->getSource()->sendMessage(C::RED . "Enchanting failed. The book has been destroyed.");
                    $event->setCancelled();
                    $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                    return;
                }

                $enchantment = new EnchantmentInstance($enchantment, $level);
                $itemClicked->addEnchantment($enchantment);
                $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);
                $enchantmentSuccessful = true;
                if ($enchantmentSuccessful) {
                    $event->setCancelled();
                    $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                    $event->getTransaction()->getSource()->sendMessage(C::GREEN . "Successfully enchanted.");
                }
            }
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     * @priority HIGH
     * @ignoreCancelled true
     */
    public function onWhiteScroll(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $oldToNew = isset(array_keys($actions)[0]) ? $actions[array_keys($actions)[0]] : null;
        $newToOld = isset(array_keys($actions)[1]) ? $actions[array_keys($actions)[1]] : null;
        if ($oldToNew instanceof SlotChangeAction && $newToOld instanceof SlotChangeAction) {
            $itemClicked = $newToOld->getSourceItem();
            $itemClickedWith = $oldToNew->getSourceItem();
            if ($itemClickedWith->getId() === $this->plugin->getConfig()->getNested("whitescroll.id") && $itemClicked->getId() !== Item::AIR) {
                if ($itemClickedWith->getNamedTagEntry("whitescroll") !== null) {
                    if ($itemClicked->getNamedTagEntry("successbook") === null) {
                        $lore = $itemClicked->getLore();
                        $lore[] = C::RESET . C::BOLD . C::WHITE . "PROTECTED";
                        $itemClicked->setLore($lore);

                        $itemClicked->setNamedTagEntry(new IntTag("protected", mt_rand(0, 100000)));
                        $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);

                        $event->setCancelled();
                        $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                    }
                }
            }
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     * @priority HIGH
     * @ignoreCancelled true
     */
    public function onEnchantOrb(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $oldToNew = isset(array_keys($actions)[0]) ? $actions[array_keys($actions)[0]] : null;
        $newToOld = isset(array_keys($actions)[1]) ? $actions[array_keys($actions)[1]] : null;
        if ($oldToNew instanceof SlotChangeAction && $newToOld instanceof SlotChangeAction) {
            $itemClicked = $newToOld->getSourceItem();
            $itemClickedWith = $oldToNew->getSourceItem();
            if (($orb = $itemClickedWith->getNamedTagEntry("armororb")) !== null) {
                if (!$itemClicked instanceof Armor) {
                    $event->getTransaction()->getSource()->sendMessage(C::RED . "You can only apply this on Armor");
                    return;
                }
                $lore = $itemClicked->getLore();
                if($itemClicked->getNamedTagEntry("orb") !== null) {
                    foreach ($lore as $key => $line) {
                        if(strpos($line," Max Enchants") !== false) {
                            unset($lore[$key]);
                            break;
                        }
                    }
                }
                $lore[] = "\n" . C::RESET . C::BOLD . C::GREEN . "+ " . $orb->getValue() . " Max Enchants";
                $itemClicked->setLore($lore);
                $itemClicked->setNamedTagEntry(new IntTag("orb", $orb->getValue()));
                $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);

                $event->setCancelled();
                $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));

            }
            if (($orb = $itemClickedWith->getNamedTagEntry("weaponorb")) !== null) {
                if (!$itemClicked instanceof Tool) {
                    $event->getTransaction()->getSource()->sendMessage(C::RED . "You can only apply this on Tools");
                    return;
                }
                if($itemClicked->getNamedTagEntry("orb") !== null) {
                    $lore = $itemClicked->getLore();
                    foreach ($lore as $key => $line) {
                        if(strpos($line," Max Enchants") !== false) {
                            unset($lore[$key]);
                            break;
                        }
                    }
                }
                $lore = $itemClicked->getLore();
                $lore[] = "\n" . C::RESET . C::BOLD . C::GREEN . "+ " . $orb->getValue() . " Max Enchants";
                $itemClicked->setLore($lore);

                $itemClicked->setNamedTagEntry(new IntTag("orb", $orb->getValue()));
                $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);

                $event->setCancelled();
                $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));

            }
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     * @priority HIGH
     * @ignoreCancelled true
     */
    public function onBlackScroll(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $oldToNew = isset(array_keys($actions)[0]) ? $actions[array_keys($actions)[0]] : null;
        $newToOld = isset(array_keys($actions)[1]) ? $actions[array_keys($actions)[1]] : null;
        if ($oldToNew instanceof SlotChangeAction && $newToOld instanceof SlotChangeAction) {
            $itemClicked = $newToOld->getSourceItem();
            $itemClickedWith = $oldToNew->getSourceItem();
            if ($itemClickedWith->getNamedTagEntry("blackscroll") !== null) {
                $nbt = $itemClickedWith->getNamedTagEntry("blackscroll");
                $enchantmentSuccessful = false;

                $enchants = $itemClicked->getEnchantments();
                if (empty($enchants)) {
                    return;
                }

                $removed = $enchants[array_rand($enchants)];
                if ($removed instanceof EnchantmentInstance) {
                    if (!$removed->getType() instanceof CustomEnchant) {
                        return;
                    }
                    $id = $removed->getId();
                    $level = $removed->getLevel();
                    $itemClicked->removeEnchantment($id);
                    $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);
                    $enchantmentSuccessful = true;
                }

                if ($enchantmentSuccessful) {
                    $event->setCancelled();
                    $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                    $inv = $oldToNew->getInventory();
                    $book = $this->plugin->enchantFactory->getCEEnchantBook($id, $level, $nbt->getValue());
                    if ($book !== null) {
                        $inv->addItem($book);
                    }
                }
            }
        }
    }

    /**
     * @param InventoryTransactionEvent $event
     * @priority HIGH
     * @ignoreCancelled true
     */
    public function onTransmogScroll(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $oldToNew = isset(array_keys($actions)[0]) ? $actions[array_keys($actions)[0]] : null;
        $newToOld = isset(array_keys($actions)[1]) ? $actions[array_keys($actions)[1]] : null;
        if ($oldToNew instanceof SlotChangeAction && $newToOld instanceof SlotChangeAction) {
            $itemClicked = $newToOld->getSourceItem();
            $itemClickedWith = $oldToNew->getSourceItem();
            if ($itemClickedWith->getNamedTagEntry("transmogscroll") !== null) {
                $enchants = $itemClicked->getEnchantments();
                $enchantmentSuccessful = false;

                if (empty($enchants)) {
                    return;
                }
                $enchants = Utils::filterDisplayedEnchants($itemClicked);
                $itemClicked->removeEnchantments();
                foreach ($enchants as $enchant) {
                    $itemClicked->addEnchantment($enchant);
                    $enchantmentSuccessful = true;
                }

                if ($enchantmentSuccessful) {
                    $event->setCancelled();
                    $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);
                    $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                }
            }
        }
    }


    /**
     * @param InventoryTransactionEvent $event
     * @priority HIGHEST
     * @ignoreCancelled true
     */
    public function onApplyDust(InventoryTransactionEvent $event): void
    {
        $transaction = $event->getTransaction();
        $actions = $transaction->getActions();
        $oldToNew = isset(array_keys($actions)[0]) ? $actions[array_keys($actions)[0]] : null;
        $newToOld = isset(array_keys($actions)[1]) ? $actions[array_keys($actions)[1]] : null;
        if ($oldToNew instanceof SlotChangeAction && $newToOld instanceof SlotChangeAction) {
            $itemClicked = $newToOld->getSourceItem();
            $itemClickedWith = $oldToNew->getSourceItem();
            if ($itemClickedWith->getId() === $this->plugin->getConfig()->getNested("enchantdust.id") && $itemClicked->getId() !== Item::AIR) {
                if ($itemClickedWith->getNamedTagEntry("enchantdust") !== null) {
                    if ($itemClicked->getNamedTagEntry("successbook") !== null) {
                        $oldLore = $itemClicked->getLore();
                        $countLine = 10000;

                        $new = (int)$itemClicked->getNamedTagEntry("successbook")->getValue() + $itemClickedWith->getNamedTagEntry("enchantdust")->getValue();

                        foreach ($oldLore as $key => $value) {
                            if (strpos($value, "Success") !== false) {
                                $countLine = $key;
                            }
                        }
                        if ($countLine !== 10000 && isset($oldLore[$countLine])) {
                            unset($oldLore[$countLine]);
                        }
                        if ($new > 100) {
                            $new = 100;
                        }
                        $oldLore[$countLine] = C::RESET . C::DARK_PURPLE . "$new% Success Rate";

                        $itemClicked->setLore($oldLore);
                        $itemClicked->setNamedTagEntry(new IntTag("successbook", $new));
                        $newToOld->getInventory()->setItem($newToOld->getSlot(), $itemClicked);
                        $enchantmentSuccessful = true;
                        if ($enchantmentSuccessful) {
                            $event->setCancelled();
                            $oldToNew->getInventory()->setItem($oldToNew->getSlot(), Item::get(Item::AIR));
                        }
                    }
                }
            }
        }
    }
}
<?php

declare(strict_types=1);

namespace Heisenburger69\CosmicEnchants;

use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchant;
use Heisenburger69\CosmicEnchants\commands\CEShopCommand;
use Heisenburger69\CosmicEnchants\commands\DustCommand;
use Heisenburger69\CosmicEnchants\commands\GiveXpCommand;
use Heisenburger69\CosmicEnchants\commands\ScrollCommand;
use Heisenburger69\CosmicEnchants\commands\TinkerCommand;
use Heisenburger69\CosmicEnchants\libs\muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase
{

    /**
     * @var EnchantFactory
     */
    public $enchantFactory;

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
	    $this->enchantFactory = new EnchantFactory($this);
	    $this->getServer()->getCommandMap()->register("CosmicEnchants", new DustCommand($this));
        $this->getServer()->getCommandMap()->register("CosmicEnchants", new ScrollCommand($this));
        $this->getServer()->getCommandMap()->register("CosmicEnchants", new CEShopCommand($this));
        $this->getServer()->getCommandMap()->register("CosmicEnchants", new GiveXpCommand($this));
        $this->getServer()->getCommandMap()->register("CosmicEnchants", new TinkerCommand($this));
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getLogger()->info(TextFormat::AQUA . "CosmicEnchants by Heisenburger69 enabled. Distribution and sale is prohibited.");

        if(!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
    }

    public function rarityToName(int $rarity): ?string
    {
        switch ($rarity) {
            case 1:
                return "Simple";
            case 2:
                return "Unique";
            case 3:
                return "Elite";
            case 4:
                return "Ultimate";
            case 5:
                return "Legendary";
            case 6:
                return "Soul";
            case 7:
                return "Heroic";
            case 10:
                return "Mastery";
        }
        return null;
    }

    public function getAllEnchantments(): array
    {
        $ids = [];
        foreach (CustomEnchantManager::getEnchantments() as $enchantment) {
            $ids[] = $enchantment->getId();
        }
        return $ids;
    }

    public function getSimpleEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 1) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

    public function getUniqueEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 2) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

    public function getEliteEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 3) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

    public function getUltimateEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 4) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

    public function getLegendaryEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 5) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

    public function getSoulEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 6) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

    public function getHeroicEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 7) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

    public function getMasteryEnchants(): array
    {
        $array = [];
        foreach ($this->getAllEnchantments() as $enchantment) {
            $enchant = CustomEnchantManager::getEnchantment($enchantment);
            if($enchant instanceof CustomEnchant) {
                if($enchant->getRarity() === 8) {
                    $array[] = $enchantment;
                }
            }
        }
        return $array;
    }

}

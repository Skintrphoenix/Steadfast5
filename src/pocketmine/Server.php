<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

/**
 * PocketMine-MP is the Minecraft: PE multiplayer server software
 * Homepage: http://www.pocketmine.net/
 */
namespace pocketmine;

use pocketmine\block\Block;
use pocketmine\command\CommandReader;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\command\SimpleCommandMap;
use pocketmine\entity\Arrow;
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\ExperienceOrb;
use pocketmine\entity\FallingSand;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\Painting;
use pocketmine\entity\PrimedTNT;
use pocketmine\entity\projectile\BottleOEnchanting;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\entity\Snowball;
use pocketmine\entity\Egg;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Minecart;
use pocketmine\entity\Boat;
use pocketmine\entity\FishingHook;
use pocketmine\event\HandlerList;
use pocketmine\event\level\LevelInitEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\server\ServerCommandEvent;
use pocketmine\event\Timings;
use pocketmine\event\TimingsHandler;
use pocketmine\inventory\CraftingManager;
use pocketmine\inventory\InventoryType;
use pocketmine\inventory\Recipe;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\inventory\FurnaceRecipe;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\level\format\anvil\Anvil;
use pocketmine\level\format\pmanvil\PMAnvil;
use pocketmine\level\format\LevelProviderManager;
use pocketmine\level\format\mcregion\McRegion;
use pocketmine\level\generator\Generator;
use pocketmine\level\Level;
use pocketmine\metadata\EntityMetadataStore;
use pocketmine\metadata\LevelMetadataStore;
use pocketmine\metadata\PlayerMetadataStore;
use pocketmine\mods\ModsManager;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\CompressBatchedTask;
use pocketmine\network\AdvancedSourceInterface;
use pocketmine\network\Network;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\CraftingDataPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\PlayerListPacket;
use pocketmine\network\query\QueryHandler;
use pocketmine\network\RakLibInterface;
use pocketmine\network\rcon\RCON;
use pocketmine\network\SourceInterface;
use pocketmine\network\upnp\UPnP;
use pocketmine\permission\BanList;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PharPluginLoader;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\plugin\PluginManager;
use pocketmine\scheduler\CallbackTask;
use pocketmine\scheduler\GarbageCollectionTask;
use pocketmine\scheduler\ServerScheduler;
use pocketmine\tile\Bed;
use pocketmine\tile\Cauldron;
use pocketmine\tile\Chest;
use pocketmine\tile\Dispenser;
use pocketmine\tile\Dropper;
use pocketmine\tile\Hopper;
use pocketmine\tile\EnchantTable;
use pocketmine\tile\EnderChest;
use pocketmine\tile\FlowerPot;
use pocketmine\tile\Furnace;
use pocketmine\tile\PistonArm;
use pocketmine\tile\ShulkerBox;
use pocketmine\tile\ItemFrame;
use pocketmine\tile\Sign;
use pocketmine\tile\Skull;
use pocketmine\tile\Tile;
use pocketmine\utils\Binary;
use pocketmine\utils\Cache;
use pocketmine\utils\Config;
use pocketmine\utils\LevelException;
use pocketmine\utils\MainLogger;
use pocketmine\utils\ServerException;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextWrapper;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use pocketmine\utils\VersionString;
use pocketmine\network\protocol\Info;
use pocketmine\level\generator\biome\Biome;
use pocketmine\scheduler\FileWriteTask;
use pocketmine\entity\animal\walking\Chicken;
use pocketmine\entity\animal\walking\Cow;
use pocketmine\entity\animal\walking\Mooshroom;
use pocketmine\entity\animal\walking\Ocelot;
use pocketmine\entity\animal\walking\Pig;
use pocketmine\entity\animal\walking\Rabbit;
use pocketmine\entity\animal\walking\Sheep;
use pocketmine\entity\monster\flying\Blaze;
use pocketmine\entity\monster\flying\Ghast;
use pocketmine\entity\monster\walking\CaveSpider;
use pocketmine\entity\monster\walking\Creeper;
use pocketmine\entity\monster\walking\Enderman;
use pocketmine\entity\monster\walking\IronGolem;
use pocketmine\entity\monster\walking\PigZombie;
use pocketmine\entity\monster\walking\Silverfish;
use pocketmine\entity\monster\walking\Skeleton;
use pocketmine\entity\monster\walking\SnowGolem;
use pocketmine\entity\monster\walking\Spider;
use pocketmine\entity\monster\walking\Wolf;
use pocketmine\entity\monster\walking\Zombie;
use pocketmine\entity\monster\walking\ZombieVillager;
use pocketmine\entity\projectile\FireBall;
use pocketmine\utils\MetadataConvertor;
use pocketmine\event\server\SendRecipiesList;
use pocketmine\network\protocol\PEPacket;
use pocketmine\scheduler\InventoryTransactionTask;
use pocketmine\tile\Beacon;
use pocketmine\tile\Banner;

/**
 * The class that manages everything
 */
class Server{
	const BROADCAST_CHANNEL_ADMINISTRATIVE = "pocketmine.broadcast.admin";
	const BROADCAST_CHANNEL_USERS = "pocketmine.broadcast.user";

	/** @var Server */
	private static $instance = null;
	
	private static $serverId =  0;

	/** @var BanList */
	private $banByName = null;

	/** @var \Threaded */
	private static $sleeper = null;

	/** @var BanList */
	private $banByIP = null;

	/** @var Config */
	private $operators = null;

	/** @var Config */
	private $whitelist = null;

	/** @var bool */
	private $isRunning = true;

	private $hasStopped = false;

	/** @var PluginManager */
	private $pluginManager = null;

	private $profilingTickRate = 20;

	/** @var ServerScheduler */
	private $scheduler = null;

	/**
	 * Counts the ticks since the server start
	 *
	 * @var int
	 */
	private $tickCounter;
	private $nextTick = 0;
	private $tickAverage = [20, 20, 20, 20, 20];
	private $useAverage = [20, 20, 20, 20, 20];

	private $currentTPS = 20;

	private $currentUse = 0;

	private $startTime;

	private $doTitleTick = true;

	private $sendUsageTicker = 0;

	/** @var \AttachableThreadedLogger */
	private $logger;

	/** @var CommandReader */
	private $console = null;

	/** @var SimpleCommandMap */
	private $commandMap = null;

	/** @var CraftingManager */
	private $craftingManager;

	/** @var ConsoleCommandSender */
	private $consoleSender;

	/** @var int */
	private $maxPlayers;

	/** @var bool */
	private $autoSave;

	/** @var RCON */
	private $rcon;
	
	/** @var bool */
	private $autoGenerate;
	
	/** @var bool */
	private $savePlayerData;

	/** @var EntityMetadataStore */
	private $entityMetadata;

	/** @var PlayerMetadataStore */
	private $playerMetadata;

	/** @var LevelMetadataStore */
	private $levelMetadata;
	
	/** @var AdvancedSourceInterface */
	private $mainInterface;

	/** @var Network */
	private $network;
	
	public $networkCompressionLevel = 7;

	private $serverID;

	private $autoloader;
	private $filePath;
	private $dataPath;
	private $pluginPath;

	/** @var QueryHandler */
	private $queryHandler;

	/** @var Config */
	private $properties;

	/** @var Config */
	private $config;

	/** @var Config */
	private $softConfig;

	/** @var Player[] */
	private $players = [];

	/** @var Player[] */
	private $playerList = [];

	private $identifiers = [];

	/** @var Level[] */
	private $levels = [];

	/** @var Level */
	private $levelDefault = null;
	
	private $useAnimal;
	private $animalLimit;
	private $useMonster ;
	private $monsterLimit;

	public $netherEnabled = false;
	public $netherName = "nether";
	public $netherLevel = null;
	public $enderEnabled = false;
	public $enderName = "ender";
	public $enderLevel = null;

	public $packetMaker = null;
	
	private $jsonCommands = [];
	private $spawnedEntity = [];
	
	private $unloadLevelQueue = [];
	
	private $serverPublicKey = '';
	private $serverPrivateKey = '';
	private $serverToken = 'hksdYI3has';
	private $isUseEncrypt = false;
	
	private $modsManager = null;

	public function addSpawnedEntity($entity) {
		if ($entity instanceof Player) {
			return;
		}
		$this->spawnedEntity[$entity->getId()] = $entity;
	}

	public function removeSpawnedEntity($entity) {
		unset($this->spawnedEntity[$entity->getId()]);
	}

	public function isUseAnimal() {
		return $this->useAnimal;
	}

	public function getAnimalLimit() {
		return $this->animalLimit;
	}

	public function isUseMonster() {
		return $this->useMonster;
	}

	public function getMonsterLimit() {
		return $this->monsterLimit;
	}
	
	/**
	 * @return string
	 */
	public function getName(){
		return "Steadfast5";
	}

	/**
	 * @return bool
	 */
	public function isRunning(){
		return $this->isRunning === true;
	}

	/**
	 * @return string
	 */
	public function getPocketMineVersion(){
		return \pocketmine\VERSION;
	}

	/**
	 * @return string
	 */
	public function getCodename(){
		return \pocketmine\CODENAME;
	}

	/**
	 * @return string
	 */
	public function getVersion(){
		return \pocketmine\MINECRAFT_VERSION;
	}

	/**
	 * @return string
	 */
	public function getApiVersion(){
		return \pocketmine\API_VERSION;
	}

	/**
	 * @return string
	 */
	public function getFilePath(){
		return $this->filePath;
	}

	/**
	 * @return string
	 */
	public function getDataPath(){
		return $this->dataPath;
	}

	/**
	 * @return string
	 */
	public function getPluginPath(){
		return $this->pluginPath;
	}

	/**
	 * @return int
	 */
	public function getMaxPlayers(){
		return $this->maxPlayers;
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->getConfigInt("server-port", 19132);
	}
	
	/**
	 * @return int
	 */
	public function getViewDistance(){
		return 96;
	}

	/**
	 * @return string
	 */
	public function getIp(){
		return $this->getConfigString("server-ip", "0.0.0.0");
	}

	/**
	 * @return string
	 */
	public function getServerName(){
		return $this->getConfigString("motd", "Minecraft: Bedrock Edition Server");
	}

	/**
	 * @return bool
	 */
	public function getAutoSave(){
		return $this->autoSave;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave($value){
		$this->autoSave = (bool) $value;
		foreach($this->getLevels() as $level){
			$level->setAutoSave($this->autoSave);
		}
	}
	
	/**
	 * @return bool
	 */
	public function getAutoGenerate(){
		return $this->autoGenerate;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoGenerate($value){
		$this->autoGenerate = (bool) $value;		
	}
	
	/**
	 * @return bool
	 */
	public function getSavePlayerData(){
		return $this->savePlayerData;
	}

	
	/**
	 * @param bool $value
	 */
	public function setSavePlayerData($value) {
		$this->savePlayerData = (bool) $value;		
	}

	/**
	 * @return string
	 */
	public function getLevelType(){
		return $this->getConfigString("level-type", "DEFAULT");
	}

	/**
	 * @return bool
	 */
	public function getGenerateStructures(){
		return $this->getConfigBoolean("generate-structures", true);
	}

	/**
	 * @return int
	 */
	public function getGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return bool
	 */
	public function getForceGamemode(){
		return $this->getConfigBoolean("force-gamemode", false);
	}

	/**
	 * Returns the gamemode text name
	 *
	 * @param int $mode
	 *
	 * @return string
	 */
	public static function getGamemodeString($mode){
		switch((int) $mode){
			case Player::SURVIVAL:
				return "SURVIVAL";
			case Player::CREATIVE:
				return "CREATIVE";
			case Player::ADVENTURE:
				return "ADVENTURE";
			case Player::SPECTATOR:
				return "SPECTATOR";
		}

		return "UNKNOWN";
	}

	/**
	 * Parses a string and returns a gamemode integer, -1 if not found
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getGamemodeFromString($str){
		switch(strtolower(trim($str))){
			case (string) Player::SURVIVAL:
			case "survival":
			case "s":
				return Player::SURVIVAL;

			case (string) Player::CREATIVE:
			case "creative":
			case "c":
				return Player::CREATIVE;

			case (string) Player::ADVENTURE:
			case "adventure":
			case "a":
				return Player::ADVENTURE;

			case (string) Player::SPECTATOR:
			case "spectator":
			case "view":
			case "v":
				return Player::SPECTATOR;
		}
		return -1;
	}

	/**
	 * @param string $str
	 *
	 * @return int
	 */
	public static function getDifficultyFromString($str){
		switch(strtolower(trim($str))){
			case "0":
			case "peaceful":
			case "p":
				return 0;

			case "1":
			case "easy":
			case "e":
				return 1;

			case "2":
			case "normal":
			case "n":
				return 2;

			case "3":
			case "hard":
			case "h":
				return 3;
		}
		return -1;
	}

	/**
	 * @return int
	 */
	public function getDifficulty(){
		return $this->getConfigInt("difficulty", 1);
	}

	/**
	 * @return bool
	 */
	public function hasWhitelist(){
		return $this->getConfigBoolean("white-list", false);
	}

	/**
	 * @return int
	 */
	public function getSpawnRadius(){
		return $this->getConfigInt("spawn-protection", 16);
	}

	/**
	 * @return bool
	 */
	public function getAllowFlight(){
		return $this->getConfigBoolean("allow-flight", false);
	}

	/**
	 * @return bool
	 */
	public function isHardcore(){
		return $this->getConfigBoolean("hardcore", false);
	}

	/**
	 * @return int
	 */
	public function getDefaultGamemode(){
		return $this->getConfigInt("gamemode", 0) & 0b11;
	}

	/**
	 * @return string
	 */
	public function getMotd(){
		return $this->getConfigString("motd", "Minecraft: Bedrock Edition Server");
	}

	/**
	 * @return \ClassLoader
	 */
	public function getLoader(){
		return $this->autoloader;
	}

	/**
	 * @return \AttachableThreadedLogger
	 */
	public function getLogger(){
		return $this->logger;
	}

	/**
	 * @return EntityMetadataStore
	 */
	public function getEntityMetadata(){
		return $this->entityMetadata;
	}

	/**
	 * @return PlayerMetadataStore
	 */
	public function getPlayerMetadata(){
		return $this->playerMetadata;
	}

	/**
	 * @return LevelMetadataStore
	 */
	public function getLevelMetadata(){
		return $this->levelMetadata;
	}

	/**
	 * @return PluginManager
	 */
	public function getPluginManager(){
		return $this->pluginManager;
	}

	/**
	 * @return CraftingManager
	 */
	public function getCraftingManager(){
		return $this->craftingManager;
	}

	/**
	 * @return ServerScheduler
	 */
	public function getScheduler(){
		return $this->scheduler;
	}
	
	/**
	 * @return ModsManager
	 */
	public function getModsManager() {
		return $this->modsManager;
	}

	/**
	 * @return int
	 */
	public function getTick(){
		return $this->tickCounter;
	}

	/**
	 * Returns the last server TPS measure
	 *
	 * @return float
	 */
	public function getTicksPerSecond(){
		return round(array_sum($this->tickAverage) / count($this->tickAverage), 2);
	}

	/**
	 * Returns the TPS usage/load in %
	 *
	 * @return float
	 */
	public function getTickUsage(){
		return round((array_sum($this->useAverage) / count($this->useAverage)) * 100, 2);
	}


	/**
	 * @deprecated
	 *
	 * @param     $address
	 * @param int $timeout
	 */
	public function blockAddress($address, $timeout = 300){
		$this->network->blockAddress($address, $timeout);
	}

	/**
	 * @deprecated
	 *
	 * @param $address
	 * @param $port
	 * @param $payload
	 */
	public function sendPacket($address, $port, $payload){
		$this->network->sendPacket($address, $port, $payload);
	}

	/**
	 * @deprecated
	 *
	 * @return SourceInterface[]
	 */
	public function getInterfaces(){
		return $this->network->getInterfaces();
	}

	/**
	 * @deprecated
	 *
	 * @param SourceInterface $interface
	 */
	public function addInterface(SourceInterface $interface){
		$this->network->registerInterface($interface);
	}

	/**
	 * @deprecated
	 *
	 * @param SourceInterface $interface
	 */
	public function removeInterface(SourceInterface $interface){
		$interface->shutdown();
		$this->network->unregisterInterface($interface);
	}

	/**
	 * @return SimpleCommandMap
	 */
	public function getCommandMap(){
		return $this->commandMap;
	}

	/**
	 * @return Player[]
	 */
	public function getOnlinePlayers(){
		return $this->playerList;
	}

	public function addRecipe(Recipe $recipe){
		$this->craftingManager->registerRecipe($recipe);
	}

	/**
	 * @param string $name
	 *
	 * @return OfflinePlayer|Player
	 */
	public function getOfflinePlayer($name){
		$name = strtolower($name);
		$result = $this->getPlayerExact($name);

		if($result === null){
			$result = new OfflinePlayer($this, $name);
		}

		return $result;
	}

	/**
	 * @param string $name
	 *
	 * @return Compound
	 */
	public function getOfflinePlayerData($name){
		$name = strtolower($name);
		$path = $this->getDataPath() . "players/";
		if(file_exists($path . "$name.dat")){
			try{
				$nbt = new NBT(NBT::BIG_ENDIAN);
				$nbt->readCompressed(file_get_contents($path . "$name.dat"));

				return $nbt->getData();
			}catch(\Exception $e){ //zlib decode error / corrupt data
				rename($path . "$name.dat", $path . "$name.dat.bak");
				$this->logger->warning("Corrupted data found for \"" . $name . "\", creating new profile");
			}
		} else {
			$this->logger->notice("Player data not found for \"" . $name . "\", creating new profile");
		}
		$spawn = $this->getDefaultLevel()->getSafeSpawn();
		$nbt = new Compound("", [
			new LongTag("firstPlayed", floor(microtime(true) * 1000)),
			new LongTag("lastPlayed", floor(microtime(true) * 1000)),
			new Enum("Pos", [
				new DoubleTag(0, $spawn->x),
				new DoubleTag(1, $spawn->y),
				new DoubleTag(2, $spawn->z)
			]),
			new StringTag("Level", $this->getDefaultLevel()->getName()),
			//new StringTag("SpawnLevel", $this->getDefaultLevel()->getName()),
			//new IntTag("SpawnX", (int) $spawn->x),
			//new IntTag("SpawnY", (int) $spawn->y),
			//new IntTag("SpawnZ", (int) $spawn->z),
			//new ByteTag("SpawnForced", 1), //TODO
			new Enum("Inventory", []),
			new Compound("Achievements", []),
			new IntTag("playerGameType", $this->getGamemode()),
			new Enum("Motion", [
				new DoubleTag(0, 0.0),
				new DoubleTag(1, 0.0),
				new DoubleTag(2, 0.0)
			]),
			new Enum("Rotation", [
				new FloatTag(0, 0.0),
				new FloatTag(1, 0.0)
			]),
			new FloatTag("FallDistance", 0.0),
			new ShortTag("Fire", 0),
			new ShortTag("Air", 300),
			new ByteTag("OnGround", 1),
			new ByteTag("Invulnerable", 0),
			new StringTag("NameTag", $name),
		]);
		$nbt->Pos->setTagType(NBT::TAG_Double);
		$nbt->Inventory->setTagType(NBT::TAG_Compound);
		$nbt->Motion->setTagType(NBT::TAG_Double);
		$nbt->Rotation->setTagType(NBT::TAG_Float);

		// $this->saveOfflinePlayerData($name, $nbt);

		return $nbt;

	}

	/**
	 * @param string   $name
	 * @param Compound $nbtTag
 	 * @param bool $async
	 */
	public function saveOfflinePlayerData($name, Compound $nbtTag, $async = false){
			$nbt = new NBT(NBT::BIG_ENDIAN);
		try{
			$nbt->setData($nbtTag);
			if($async){
				$this->getScheduler()->scheduleAsyncTask(new FileWriteTask($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed()));
			} else {
				file_put_contents($this->getDataPath() . "players/" . strtolower($name) . ".dat", $nbt->writeCompressed());
			}
		}catch(\Exception $e){
			$this->logger->critical("Could not save player " . $name . ": " . $e->getMessage());
			if(\pocketmine\DEBUG > 1 and $this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return Player|null
	 */
	public function getPlayer($name){
		$found = null;
		$name = strtolower($name);
		$delta = PHP_INT_MAX;
		foreach ($this->getOnlinePlayers() as $player) {
			$playerName = strtolower($player->getName());
			if (strpos($playerName, $name) === 0) {
				$curDelta = strlen($playerName) - strlen($name);
				if ($curDelta < $delta) {
					$found = $player;
					$delta = $curDelta;
				}
				if ($curDelta == 0) {
					break;
				}
			}
		}

		return $found;
	}

	public static function microSleep(int $microseconds){
		Server::$sleeper->synchronized(function(int $ms){
			Server::$sleeper->wait($ms);
		}, $microseconds);
	}

	/**
	 * @param string $name
	 *
	 * @return Player
	 */
	public function getPlayerExact($name){
		$name = strtolower($name);
		foreach($this->getOnlinePlayers() as $player){
			if(strtolower($player->getName()) === $name){
				return $player;
			}
		}

		return null;
	}

	/**
	 * @param string $partialName
	 *
	 * @return Player[]
	 */
	public function matchPlayer($partialName){
		$partialName = strtolower($partialName);
		$matchedPlayers = [];
		foreach($this->getOnlinePlayers() as $player){
			$playerName = strtolower($player->getName());
			if ($playerName === $partialName) {
				$matchedPlayers = [$player];
				break;
			} else if (strpos($playerName, $partialName) !== false) {
				$matchedPlayers[] = $player;
			}
		}

		return $matchedPlayers;
	}

	/**
	 * @param Player $player
	 */
	public function removePlayer(Player $player){
		if(isset($this->identifiers[$hash = spl_object_hash($player)])){
			$identifier = $this->identifiers[$hash];
			unset($this->players[$identifier]);
			unset($this->identifiers[$hash]);
			return;
		}

		foreach($this->players as $identifier => $p){
			if($player === $p){
				unset($this->players[$identifier]);
				unset($this->identifiers[spl_object_hash($player)]);
				break;
			}
		}
	}

	/**
	 * @return Level[]
	 */
	public function getLevels(){
		return $this->levels;
	}

	/**
	 * @return Level
	 */
	public function getDefaultLevel(){
		return $this->levelDefault;
	}

	/**
	 * Sets the default level to a different level
	 * This won't change the level-name property,
	 * it only affects the server on runtime
	 *
	 * @param Level $level
	 */
	public function setDefaultLevel($level){
		if($level === null or ($this->isLevelLoaded($level->getFolderName()) and $level !== $this->levelDefault)){
			$this->levelDefault = $level;
		}
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelLoaded($name){
		return $this->getLevelByName($name) instanceof Level;
	}

	/**
	 * @param int $levelId
	 *
	 * @return Level
	 */
	public function getLevel($levelId){
		if(isset($this->levels[$levelId])){
			return $this->levels[$levelId];
		}

		return null;
	}

	/**
	 * @param $name
	 *
	 * @return Level
	 */
	public function getLevelByName($name){
		foreach($this->getLevels() as $level){
			if($level->getFolderName() === $name){
				return $level;
			}
		}

		return null;
	}

	/**
	 * @param Level $level
	 * @param bool  $forceUnload
	 *
	 * @return bool
	 */
	public function unloadLevel(Level $level, $forceUnload = false, $direct = false){
		if ($direct) {
			if($level->unload($forceUnload) === true){
				unset($this->levels[$level->getId()]);
				return true;
			}
		} else {
			$this->unloadLevelQueue[$level->getId()] = ['level' => $level, 'force' => $forceUnload];
		}

		return false;
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @param string $name
	 *
	 * @return bool
	 *
	 * @throws LevelException
	 */
	public function loadLevel($name){
		if(trim($name) === ""){
			throw new LevelException("Invalid empty level name");
		}
		if($this->isLevelLoaded($name)){
			return true;
		} elseif(!$this->isLevelGenerated($name)){
			$this->logger->notice("Level \"" . $name . "\" not found");

			return false;
		}

		$path = $this->getDataPath() . "worlds/" . $name . "/";

		$provider = LevelProviderManager::getProvider($path);

		if($provider === null){
			$this->logger->error("Could not load level \"" . $name . "\": Unknown provider");

			return false;
		}
		//$entities = new Config($path."entities.yml", Config::YAML);
		//if(file_exists($path . "tileEntities.yml")){
		//	@rename($path . "tileEntities.yml", $path . "tiles.yml");
		//}

		try{
			$level = new Level($this, $name, $path, $provider);
		}catch(\Exception $e){

			$this->logger->error("Could not load level \"" . $name . "\": " . $e->getMessage());
			if($this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
			return false;
		}

		$this->levels[$level->getId()] = $level;

		$level->initLevel();

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));
		return true;
	}

	/**
	 * Generates a new level if it does not exists
	 *
	 * @param string $name
	 * @param int    $seed
	 * @param array  $options
	 *
	 * @return bool
	 */
	public function generateLevel($name, $seed = null, $options = []){
		if(trim($name) === "" or $this->isLevelGenerated($name)){
			return false;
		}

		$seed = $seed === null ? Binary::readInt(@Utils::getRandomBytes(4, false)) : (int) $seed;

		if(($provider = LevelProviderManager::getProviderByName($providerName = $this->getProperty("level-settings.default-format", "mcregion"))) === null){
			$provider = LevelProviderManager::getProviderByName($providerName = "mcregion");
		}

		try{
			$path = $this->getDataPath() . "worlds/" . $name . "/";
			/** @var \pocketmine\level\format\LevelProvider $provider */
			$provider::generate($path, $name, $seed, $options);

			$level = new Level($this, $name, $path, $provider);
			$this->levels[$level->getId()] = $level;

			$level->initLevel();
		}catch(\Exception $e){
			$this->logger->error("Could not generate level \"" . $name . "\": " . $e->getMessage());
			if($this->logger instanceof MainLogger){
				$this->logger->logException($e);
			}
			return false;
		}

		$this->getPluginManager()->callEvent(new LevelInitEvent($level));

		$this->getPluginManager()->callEvent(new LevelLoadEvent($level));
		
		if ($this->getAutoGenerate()) {
			$centerX = $level->getSpawnLocation()->getX() >> 4;
			$centerZ = $level->getSpawnLocation()->getZ() >> 4;

			$order = [];

			for($X = -3; $X <= 3; ++$X){
				for($Z = -3; $Z <= 3; ++$Z){
					$distance = $X ** 2 + $Z ** 2;
					$chunkX = $X + $centerX;
					$chunkZ = $Z + $centerZ;
					$index = Level::chunkHash($chunkX, $chunkZ);
					$order[$index] = $distance;
				}
			}

			asort($order);

			foreach($order as $index => $distance){
				Level::getXZ($index, $chunkX, $chunkZ);
				$level->generateChunk($chunkX, $chunkZ, true);
			}
		}

		return true;
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isLevelGenerated($name){
		if(trim($name) === ""){
			return false;
		}
		$path = $this->getDataPath() . "worlds/" . $name . "/";
		if(!($this->getLevelByName($name) instanceof Level)){

			if(LevelProviderManager::getProvider($path) === null){
				return false;
			}
			/*if(file_exists($path)){
				$level = new LevelImport($path);
				if($level->import() === false){ //Try importing a world
					return false;
				}
			} else {
				return false;
			}*/
		}

		return true;
	}

	/**
	 * @param string $variable
	 * @param string $defaultValue
	 *
	 * @return string
	 */
	public function getConfigString($variable, $defaultValue = ""){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (string) $v[$variable];
		}

		return $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getAdvancedProperty($variable, $defaultValue = null) {
		$vars = explode(".", $variable);
		$base = array_shift($vars);
		if ($this->softConfig->exists($base)) {
			$base = $this->softConfig->get($base);
		} else {
			return $defaultValue;
		}

		while (count($vars) > 0) {
			$baseKey = array_shift($vars);
			if (is_array($base) && isset($base[$baseKey])) {
				$base = $base[$baseKey];
			} else {
				return $defaultValue;
			}
		}

		return $base;
	}


	/**
	 * @param string $variable
	 * @param mixed  $defaultValue
	 *
	 * @return mixed
	 */
	public function getProperty($variable, $defaultValue = null){
		$value = $this->config->getNested($variable);

		return $value === null ? $defaultValue : $value;
	}

	/**
	 * @param string $variable
	 * @param string $value
	 */
	public function setConfigString($variable, $value){
		$this->properties->set($variable, $value);
	}

	/**
	 * @param string $variable
	 * @param int    $defaultValue
	 *
	 * @return int
	 */
	public function getConfigInt($variable, $defaultValue = 0){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			return (int) $v[$variable];
		}

		return $this->properties->exists($variable) ? (int) $this->properties->get($variable) : (int) $defaultValue;
	}

	/**
	 * @param string $variable
	 * @param int    $value
	 */
	public function setConfigInt($variable, $value){
		$this->properties->set($variable, (int) $value);
	}

	/**
	 * @param string  $variable
	 * @param boolean $defaultValue
	 *
	 * @return boolean
	 */
	public function getConfigBoolean($variable, $defaultValue = false){
		$v = getopt("", ["$variable::"]);
		if(isset($v[$variable])){
			$value = $v[$variable];
		} else {
			$value = $this->properties->exists($variable) ? $this->properties->get($variable) : $defaultValue;
		}

		if(is_bool($value)){
			return $value;
		}
		switch(strtolower($value)){
			case "on":
			case "true":
			case "1":
			case "yes":
				return true;
		}

		return false;
	}

	/**
	 * @param string $variable
	 * @param bool   $value
	 */
	public function setConfigBool($variable, $value){
		$this->properties->set($variable, $value == true ? "1" : "0");
	}

	/**
	 * @param string $name
	 *
	 * @return PluginIdentifiableCommand
	 */
	public function getPluginCommand($name){
		if(($command = $this->commandMap->getCommand($name)) instanceof PluginIdentifiableCommand){
			return $command;
		} else {
			return null;
		}
	}

	/**
	 * @return BanList
	 */
	public function getNameBans(){
		return $this->banByName;
	}

	/**
	 * @return BanList
	 */
	public function getIPBans(){
		return $this->banByIP;
	}

	/**
	 * @param string $name
	 */
	public function addOp($name){
		$this->operators->set(strtolower($name), true);

		if(($player = $this->getPlayerExact($name)) instanceof Player){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function removeOp($name){
		$this->operators->remove(strtolower($name));

		if(($player = $this->getPlayerExact($name)) instanceof Player){
			$player->recalculatePermissions();
		}
		$this->operators->save();
	}

	/**
	 * @param string $name
	 */
	public function addWhitelist($name){
		$this->whitelist->set(strtolower($name), true);
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 */
	public function removeWhitelist($name){
		$this->whitelist->remove(strtolower($name));
		$this->whitelist->save();
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isWhitelisted($name){
		return !$this->hasWhitelist() or $this->operators->exists($name, true) or $this->whitelist->exists($name, true);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isOp($name){
		return $this->operators->exists($name, true);
	}

	/**
	 * @return Config
	 */
	public function getWhitelisted(){
		return $this->whitelist;
	}

	/**
	 * @return Config
	 */
	public function getOps(){
		return $this->operators;
	}

	public function reloadWhitelist(){
		$this->whitelist->reload();
	}

	/**
	 * @return string[]
	 */
	public function getCommandAliases(){
		$section = $this->getProperty("aliases");
		$result = [];
		if(is_array($section)){
			foreach($section as $key => $value){
				$commands = [];
				if(is_array($value)){
					$commands = $value;
				} else {
					$commands[] = $value;
				}

				$result[$key] = $commands;
			}
		}

		return $result;
	}

	/**
	 * @return Server
	 */
	public static function getInstance(){
		return self::$instance;
	}
	
	public static function getServerId(){
		return self::$serverId;
	}

	/**
	 * @param \ClassLoader    $autoloader
	 * @param \ThreadedLogger $logger
	 * @param string          $filePath
	 * @param string          $dataPath
	 * @param string          $pluginPath
	 */
	public function __construct(\ClassLoader $autoloader, \ThreadedLogger $logger, $filePath, $dataPath, $pluginPath){
//		$this->test();
//		return;
		self::$instance = $this;
		self::$serverId =  mt_rand(0, PHP_INT_MAX);

		$this->autoloader = $autoloader;
		self::$sleeper = new \Threaded;
		$this->logger = $logger;
		$this->filePath = $filePath;
		if(!file_exists($dataPath . "worlds/")){
			mkdir($dataPath . "worlds/", 0777);
		}

		if(!file_exists($dataPath . "players/")){
			mkdir($dataPath . "players/", 0777);
		}

		if(!file_exists($pluginPath)){
			mkdir($pluginPath, 0777);
		}

		$this->dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;
		$this->pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

		$this->console = new CommandReader();

		$version = new VersionString($this->getPocketMineVersion());
		$this->logger->info("Starting Minecraft: Bedrock Edition server version " . TextFormat::AQUA . $this->getVersion());

		$this->logger->info("Loading pocketmine-soft.yml...");
		if(!file_exists($this->dataPath . "pocketmine-soft.yml")){
			$content = file_get_contents($this->filePath . "src/pocketmine/resources/pocketmine-soft.yml");
			@file_put_contents($this->dataPath . "pocketmine-soft.yml", $content);
		}
		$this->softConfig = new Config($this->dataPath . "pocketmine-soft.yml", Config::YAML, []);
		
		$this->logger->info("Loading pocketmine.yml...");
		if(!file_exists($this->dataPath . "pocketmine.yml")){
			$content = file_get_contents($this->filePath . "src/pocketmine/resources/pocketmine.yml");
			@file_put_contents($this->dataPath . "pocketmine.yml", $content);
		}
		$this->config = new Config($this->dataPath . "pocketmine.yml", Config::YAML, []);

		$this->logger->info("Loading server properties...");
		$this->properties = new Config($this->dataPath . "server.properties", Config::PROPERTIES, [
			"motd" => "A Steadfast5 Minecraft: Bedrock Edition server",
			"server-port" => 19132,
			"memory-limit" => "256M",
			"white-list" => false,
			"spawn-protection" => 16,
			"max-players" => 20,
			"allow-flight" => false,
			"spawn-animals" => true,
			"animals-limit" => 0,
			"spawn-mobs" => true,
			"mobs-limit" => 0,
			"gamemode" => 0,
			"force-gamemode" => false,
			"hardcore" => false,
			"pvp" => true,
			"difficulty" => 1,
			"generator-settings" => "",
			"level-name" => "world",
			"level-seed" => "",
			"level-type" => "DEFAULT",
			"enable-query" => true,
			"enable-rcon" => false,
			"rcon.password" => substr(base64_encode(@Utils::getRandomBytes(20, false)), 3, 10),
			"auto-save" => true,
			"auto-generate" => true,
			"save-player-data" => true,
			"time-update" => true,
			"use-encrypt" => false
		]);

		ServerScheduler::$WORKERS = 4;

		$this->scheduler = new ServerScheduler();

		if($this->getConfigBoolean("enable-rcon", false) === true){
			$this->rcon = new RCON($this, $this->getConfigString("rcon.password", ""), $this->getConfigInt("rcon.port", $this->getPort()), ($ip = $this->getIp()) != "" ? $ip : "0.0.0.0", $this->getConfigInt("rcon.threads", 1), $this->getConfigInt("rcon.clients-per-thread", 50));
		}

		$this->entityMetadata = new EntityMetadataStore();
		$this->playerMetadata = new PlayerMetadataStore();
		$this->levelMetadata = new LevelMetadataStore();

		$this->operators = new Config($this->dataPath . "ops.txt", Config::ENUM);
		$this->whitelist = new Config($this->dataPath . "white-list.txt", Config::ENUM);
		if(file_exists($this->dataPath . "banned.txt") and !file_exists($this->dataPath . "banned-players.txt")){
			@rename($this->dataPath . "banned.txt", $this->dataPath . "banned-players.txt");
		}
		@touch($this->dataPath . "banned-players.txt");
		$this->banByName = new BanList($this->dataPath . "banned-players.txt");
		$this->banByName->load();
		@touch($this->dataPath . "banned-ips.txt");
		$this->banByIP = new BanList($this->dataPath . "banned-ips.txt");
		$this->banByIP->load();

		$this->maxPlayers = $this->getConfigInt("max-players", 20);
		$this->setAutoSave($this->getConfigBoolean("auto-save", true));
		$this->setAutoGenerate($this->getConfigBoolean("auto-generate", true));
		$this->setSavePlayerData($this->getConfigBoolean("save-player-data", true));
		
		$this->useAnimal = $this->getConfigBoolean("spawn-animals", true);
		$this->animalLimit = $this->getConfigInt("animals-limit", 0);
		$this->useMonster = $this->getConfigBoolean("spawn-mobs", true);
		$this->monsterLimit = $this->getConfigInt("mobs-limit", 0);
		$this->isUseEncrypt = $this->getConfigBoolean("use-encrypt", false);

		if(($memory = str_replace("B", "", strtoupper($this->getConfigString("memory-limit", "256M")))) !== false){
			$value = ["M" => 1, "G" => 1024];
			$real = ((int) substr($memory, 0, -1)) * $value[substr($memory, -1)];
			if($real < 128){
				$this->logger->warning($this->getName() . " may not work right with less than 128MB of RAM");
			}
			@ini_set("memory_limit", $memory);
		} else {
			$this->setConfigString("memory-limit", "256M");
		}
		$this->network = new Network($this);
		$this->network->setName($this->getMotd());

		if($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3){
			$this->setConfigInt("difficulty", 3);
		}

		define("pocketmine\\DEBUG", (int) $this->getProperty("debug.level", 1));
		if($this->logger instanceof MainLogger){
			$this->logger->setLogDebug(\pocketmine\DEBUG > 1);
		}

		Level::$COMPRESSION_LEVEL = $this->getProperty("chunk-sending.compression-level", 8);

		if(defined("pocketmine\\DEBUG") and \pocketmine\DEBUG >= 0){
			@\cli_set_process_title($this->getName() . " " . $this->getPocketMineVersion());
		}

		$this->logger->info("Starting Minecraft Bedrock Edition server on " . ($this->getIp() === "" ? "*" : $this->getIp()) . ":" . $this->getPort());
		define("BOOTUP_RANDOM", @Utils::getRandomBytes(16));
		$this->serverID = Utils::getMachineUniqueId($this->getIp() . $this->getPort());
	
		$this->addInterface($this->mainInterface = new RakLibInterface($this));

		$this->logger->info("This server is running " . $this->getName() . " version " . ($version->isDev() ?  : "") . $version->get(true) . " \"" . $this->getCodename() . "\" API: " . $this->getApiVersion() . "");
		$this->logger->info($this->getName() . " is distributed under the LGPL License");

		PluginManager::$pluginParentTimer = new TimingsHandler("** Plugins");
		Timings::init();

		$this->consoleSender = new ConsoleCommandSender();
		$this->commandMap = new SimpleCommandMap($this);

		$this->registerEntities();
		$this->registerTiles();

		InventoryType::init();
		Block::init();
		Enchantment::init();
		Item::init();
		Biome::init();
		TextWrapper::init();
		MetadataConvertor::init();
		$this->craftingManager = new CraftingManager();
		PEPacket::initPallet();

		$this->pluginManager = new PluginManager($this, $this->commandMap);
		$this->pluginManager->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this->consoleSender);
//		$this->pluginManager->setUseTimings($this->getProperty("settings.enable-profiling", false));
		$this->pluginManager->setUseTimings(true);
		$this->pluginManager->registerInterface(PharPluginLoader::class);

		\set_exception_handler([$this, "exceptionHandler"]);
		register_shutdown_function([$this, "crashDump"]);

		$plugins = $this->pluginManager->loadPlugins($this->pluginPath);

		$configPlugins = $this->getAdvancedProperty("plugins", []);
		if(count($configPlugins) > 0){
			$this->getLogger()->info("Checking extra plugins");
			$loadNew = false;
			foreach($configPlugins as $plugin => $download){
				if(!isset($plugins[$plugin])){
					$path = $this->pluginPath . "/". $plugin . ".phar";
					if(substr($download, 0, 4) === "http"){
						$this->getLogger()->info("Downloading ". $plugin);
						file_put_contents($path, Utils::getURL($download));
					} else {
						file_put_contents($path, file_get_contents($download));
					}
					$loadNew = true;
				}
			}

			if($loadNew){
				$this->pluginManager->loadPlugins($this->pluginPath);
			}
		}

		$this->enablePlugins(PluginLoadOrder::STARTUP);

		LevelProviderManager::addProvider($this, Anvil::class);
		LevelProviderManager::addProvider($this, PMAnvil::class);
		LevelProviderManager::addProvider($this, McRegion::class);
		
		foreach((array) $this->getProperty("worlds", []) as $name => $worldSetting){
			if($this->loadLevel($name) === false){
				$seed = $this->getProperty("worlds.$name.seed", time());
				$options = explode(":", $this->getProperty("worlds.$name.generator", Generator::getGenerator("default")));
				if(count($options) > 0){
					$options = [
						"preset" => implode(":", $options),
					];
				} else {
					$options = [];
				}

				$this->generateLevel($name, $seed, $options);
			}
		}

		if($this->getDefaultLevel() === null){
			$default = $this->getConfigString("level-name", "world");
			if(trim($default) == ""){
				$this->getLogger()->warning("level-name cannot be null, using default");
				$default = "world";
				$this->setConfigString("level-name", "world");
			}
			if($this->loadLevel($default) === false){
				$seed = $this->getConfigInt("level-seed", time());
				$this->generateLevel($default, $seed === 0 ? time() : $seed);
			}

			$this->setDefaultLevel($this->getLevelByName($default));
		}


		$this->properties->save();

		if(!($this->getDefaultLevel() instanceof Level)){
			$this->getLogger()->emergency("No default level has been loaded");
			$this->forceShutdown();

			return;
		}

		$this->scheduler->scheduleDelayedRepeatingTask(new CallbackTask([Cache::class, "cleanup"]), $this->getProperty("ticks-per.cache-cleanup", 900), $this->getProperty("ticks-per.cache-cleanup", 900));
		if($this->getAutoSave() and $this->getProperty("ticks-per.autosave", 6000) > 0){
			$this->scheduler->scheduleDelayedRepeatingTask(new CallbackTask([$this, "doAutoSave"]), $this->getProperty("ticks-per.autosave", 6000), $this->getProperty("ticks-per.autosave", 6000));
		}

		if($this->getProperty("chunk-gc.period-in-ticks", 600) > 0){
			$this->scheduler->scheduleDelayedRepeatingTask(new CallbackTask([$this, "doLevelGC"]), $this->getProperty("chunk-gc.period-in-ticks", 600), $this->getProperty("chunk-gc.period-in-ticks", 600));
		}

		$this->scheduler->scheduleRepeatingTask(new GarbageCollectionTask(), 900);

		$this->enablePlugins(PluginLoadOrder::POSTWORLD);

		if($this->getAdvancedProperty("main.player-shuffle", 0) > 0){
			$this->scheduler->scheduleDelayedRepeatingTask(new CallbackTask([$this, "shufflePlayers"]), $this->getAdvancedProperty("main.player-shuffle", 0), $this->getAdvancedProperty("main.player-shuffle", 0));
		}

		$this->scheduler->scheduleRepeatingTask(new InventoryTransactionTask(), 2);

		$this->modsManager = new ModsManager();

		$this->start();
	}

	/**
	 * @return AdvancedSourceInterface
	 */
	public function getMainInterface() {
		return $this->mainInterface;
	}

	/**
	 * @param string        $message
	 * @param Player[]|null $recipients
	 *
	 * @return int
	 */
	public function broadcastMessage($message, $recipients = null){
		if(!is_array($recipients)){
			return $this->broadcast($message, self::BROADCAST_CHANNEL_USERS);
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * @param string        $tip
	 * @param Player[]|null $recipients
	 *
	 * @return int
	 */
	public function broadcastTip($tip, $recipients = null){
		if(!is_array($recipients)){
			/** @var Player[] $recipients */
			$recipients = [];

			foreach($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendTip($tip);
		}

		return count($recipients);
	}

	/**
	 * @param string        $popup
	 * @param Player[]|null $recipients
	 *
	 * @return int
	 */
	public function broadcastPopup($popup, $recipients = null){
		if(!is_array($recipients)){
			/** @var Player[] $recipients */
			$recipients = [];

			foreach($this->pluginManager->getPermissionSubscriptions(self::BROADCAST_CHANNEL_USERS) as $permissible){
				if($permissible instanceof Player and $permissible->hasPermission(self::BROADCAST_CHANNEL_USERS)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		/** @var Player[] $recipients */
		foreach($recipients as $recipient){
			$recipient->sendPopup($popup);
		}

		return count($recipients);
	}

	/**
	 * @param string $message
	 * @param string $permissions
	 *
	 * @return int
	 */
	public function broadcast($message, $permissions){
		/** @var CommandSender[] $recipients */
		$recipients = [];
		foreach(explode(";", $permissions) as $permission){
			foreach($this->pluginManager->getPermissionSubscriptions($permission) as $permissible){
				if($permissible instanceof CommandSender and $permissible->hasPermission($permission)){
					$recipients[spl_object_hash($permissible)] = $permissible; // do not send messages directly, or some might be repeated
				}
			}
		}

		foreach($recipients as $recipient){
			$recipient->sendMessage($message);
		}

		return count($recipients);
	}

	/**
	 * Broadcasts a Minecraft packet to a list of players
	 *
	 * @param Player[]   $players
	 * @param DataPacket $packet
	 */
	public static function broadcastPacket(array $players, DataPacket $packet) {
		$readyPackets = [];
		foreach($players as $player){
			$protocol = $player->getPlayerProtocol();
			$subClientId = $player->getSubClientId();
			$playerIndex = ($protocol << 4) | $subClientId;
			if (!isset($readyPackets[$playerIndex])) {
				$packet->senderSubClientID = $subClientId;
				$packet->encode($protocol);
				$readyPackets[$playerIndex] = $packet->getBuffer();
			}
			$player->addBufferToPacketQueue($readyPackets[$playerIndex]);
		}
	}

	/**
	 * Broadcasts a Minecraft packet to a list of players with delay
	 *
	 * @param Player[]   $players
	 * @param DataPacket $packet
	 * @param integer $delay
	 */
	public static function broadcastDelayedPacket(array $players, DataPacket $packet, $delay = 1) {
		$readyPackets = [];
		foreach($players as $player){
			$protocol = $player->getPlayerProtocol();
			$subClientId = $player->getSubClientId();
			$playerIndex = ($protocol << 4) | $subClientId;
			if (!isset($readyPackets[$playerIndex])) {
				$packet->senderSubClientID = $subClientId;
				$packet->encode($protocol);
				$readyPackets[$playerIndex] = $packet->getBuffer();
			}
			$player->addDelayedPacket($readyPackets[$playerIndex], $delay);
		}
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param Player[]            $players
	 * @param DataPacket[]|string $packets
	 */
	public function batchPackets(array $players, array $packets){
		$playersCount = count($players);
		foreach ($packets as $pk) {
			if ($playersCount < 2) {
				foreach ($players as $p) {
					$pk->setDeviceId($p->getDeviceOS());
					$p->dataPacket($pk);
				}
			} else {
				Server::broadcastPacket($players, $pk);
			}
		}
	}

	/**
	 * Broadcasts a list of packets in a batch to a list of players
	 *
	 * @param Player[]     $players
	 * @param DataPacket[] $packets
	 * @param bool         $forceSync
	 * @param bool         $immediate
	 */
	public function batchPacket(array $players, array $packets, bool $immediate = false) {
		if (empty($packets)) {
			throw new \InvalidArgumentException("Cannot send empty batch");
		}

		Timings::$playerNetworkTimer->startTiming();
		$targets = array_filter($players, function(Player $player) : bool {
			return $player->isConnected(); 
		});

		if (!empty($targets)) {
			$pk = new BatchPacket();
			foreach ($packets as $p) {
				$pk->addPacket($p);
			}

			if(Network::$BATCH_THRESHOLD >= 0 && strlen($pk->payload) >= Network::$BATCH_THRESHOLD){
				$pk->setCompressionLevel($this->networkCompressionLevel);
			} else {
				$pk->setCompressionLevel(0); //Do not compress packets under the threshold
			}

			$this->broadcastPacketsCallback($pk, $targets, $immediate);
		}
		Timings::$playerNetworkTimer->stopTiming();
	}

	/**
	 * @param BatchPacket $pk
	 * @param Player[]    $players
	 */
	public function broadcastPacketsCallback(BatchPacket $pk, array $players) {
		foreach ($players as $i) {
			$i->dataPacket($pk);
		}
	}

	/**
	 * @param int $type
	 */
	public function enablePlugins($type){
		foreach($this->pluginManager->getPlugins() as $plugin){
			if(!$plugin->isEnabled() and $plugin->getDescription()->getOrder() === $type){
				$this->enablePlugin($plugin);
			}
		}

		if($type === PluginLoadOrder::POSTWORLD){
			$this->commandMap->registerServerAliases();
			DefaultPermissions::registerCorePermissions();
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public function enablePlugin(Plugin $plugin){
		$this->pluginManager->enablePlugin($plugin);
	}

	/**
	 * @param Plugin $plugin
	 *
	 * @deprecated
	 */
	public function loadPlugin(Plugin $plugin){
		$this->enablePlugin($plugin);
	}

	public function disablePlugins(){
		$this->pluginManager->disablePlugins();
	}

	public function checkConsole(){
		//Timings::$serverCommandTimer->startTiming();
		if(($line = $this->console->getLine()) !== null){
			$this->pluginManager->callEvent($ev = new ServerCommandEvent($this->consoleSender, $line));
			if(!$ev->isCancelled()){
				$this->dispatchCommand($ev->getSender(), $ev->getCommand());
			}
		}
		//Timings::$serverCommandTimer->stopTiming();
	}

	/**
	 * Executes a command from a CommandSender
	 *
	 * @param CommandSender $sender
	 * @param string        $commandLine
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function dispatchCommand(CommandSender $sender, $commandLine){
		if(!($sender instanceof CommandSender)){
			throw new ServerException("CommandSender is not valid");
		}

		if($this->commandMap->dispatch($sender, $commandLine)){
			return true;
		}

		if($sender instanceof Player){
			$message = $this->getAdvancedProperty("messages.unknown-command", "Unknown command. Type \"/help\" for help.");
			if(is_string($message) and strlen($message) > 0){
				$sender->sendMessage(TextFormat::RED.$message);
			}
		 }else {
			$sender->sendMessage("Unknown command. Type \"help\" for help.");
		}

		return false;
	}

	public function reload(){
		$this->logger->info("Saving levels...");

		foreach($this->levels as $level){
			$level->save();
		}

		$this->pluginManager->disablePlugins();
		$this->pluginManager->clearPlugins();
		$this->commandMap->clearCommands();

		$this->logger->info("Reloading properties...");
		$this->properties->reload();
		$this->maxPlayers = $this->getConfigInt("max-players", 20);

		if(($memory = str_replace("B", "", strtoupper($this->getConfigString("memory-limit", "256M")))) !== false){
			$value = ["M" => 1, "G" => 1024];
			$real = ((int) substr($memory, 0, -1)) * $value[substr($memory, -1)];
			if($real < 256){
				$this->logger->warning($this->getName() . " may not work right with less than 256MB of RAM");
			}
			@ini_set("memory_limit", $memory);
		} else {
			$this->setConfigString("memory-limit", "256M");
		}

		if($this->getConfigBoolean("hardcore", false) === true and $this->getDifficulty() < 3){
			$this->setConfigInt("difficulty", 3);
		}

		$this->banByIP->load();
		$this->banByName->load();
		$this->reloadWhitelist();
		$this->operators->reload();

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->blockAddress($entry->getName(), -1);
		}

		$this->pluginManager->registerInterface(PharPluginLoader::class);
		$this->pluginManager->loadPlugins($this->pluginPath);
		$this->enablePlugins(PluginLoadOrder::STARTUP);
		$this->enablePlugins(PluginLoadOrder::POSTWORLD);
		TimingsHandler::reload();
	}

	/**
	 * Shutdowns the server correctly
	 */
	public function shutdown(){
		$this->isRunning = false;
		\gc_collect_cycles();
	}

	public function forceShutdown(){
		if($this->hasStopped){
			return;
		}

		try{
			$this->hasStopped = true;
			
			foreach($this->players as $player){
				$player->close(TextFormat::YELLOW . $player->getName() . " has left the game", $this->getProperty("settings.shutdown-message", "Server closed"));
			}

			foreach($this->network->getInterfaces() as $interface){
				$interface->shutdown();
				$this->network->unregisterInterface($interface);
			}

			$this->shutdown();
			if($this->rcon instanceof RCON){
				$this->rcon->stop();
			}

			if($this->getProperty("settings.upnp-forwarding", false) === true){
				$this->logger->info("[UPnP] Removing port forward...");
				UPnP::RemovePortForward($this->getPort());
			}

			$this->pluginManager->disablePlugins();

			foreach($this->getLevels() as $level){
				$this->unloadLevel($level, true, true);
			}

			HandlerList::unregisterAll();

			$this->scheduler->cancelAllTasks();
			$this->scheduler->mainThreadHeartbeat(PHP_INT_MAX);

			$this->properties->save();

			$this->console->shutdown();
			$this->console->notify();			
		}catch(\Exception $e){
			$this->logger->emergency("Crashed while crashing, killing process");
			@kill(getmypid());
		}

	}

	/**
	 * Starts the PocketMine-MP server and starts processing ticks and packets
	 */
	public function start(){			
		DataPacket::initPackets();
		if ($this->isUseEncrypt) {
			\McpeEncrypter::generateKeyPair($this->serverPrivateKey, $this->serverPublicKey);
		}
		$jsonCommands = @json_decode(@file_get_contents(__DIR__ . "/command/commands.json"), true);
		if ($jsonCommands) {
			$this->jsonCommands = $jsonCommands;
		}
		if($this->getConfigBoolean("enable-query", true) === true){
			$this->queryHandler = new QueryHandler();
		}

		foreach($this->getIPBans()->getEntries() as $entry){
			$this->network->blockAddress($entry->getName(), -1);
		}

		if($this->getProperty("settings.upnp-forwarding", false) == true){
			$this->logger->info("[UPnP] Trying to port forward...");
			UPnP::PortForward($this->getPort());
		}

		$this->tickCounter = 0;

		if(function_exists("pcntl_signal")){
			pcntl_signal(SIGTERM, [$this, "handleSignal"]);
			pcntl_signal(SIGINT, [$this, "handleSignal"]);
			pcntl_signal(SIGHUP, [$this, "handleSignal"]);
			$this->getScheduler()->scheduleRepeatingTask(new CallbackTask("pcntl_signal_dispatch"), 5);
		}


		$this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "checkTicks"]), 20 * 5);

		$this->logger->info("Default game type: " . self::getGamemodeString($this->getGamemode()));

		Effect::init();

		$this->logger->info("Done (" . round(microtime(true) - \pocketmine\START_TIME, 3) . 's)! For help, type "help" or "?"');

		$this->packetMaker = new PacketMaker($this->getLoader(), $this->mainInterface->getRakLib());
		
		$this->tickAverage = array();
		$this->useAverage = array();
		for($i = 0; $i < 1200; $i++) {
			$this->tickAverage[] = 20;
			$this->useAverage[] = 0;
		}

		$this->tickProcessor();
		$this->forceShutdown();

		\gc_collect_cycles();
	}

	public function handleSignal($signo){
		if($signo === SIGTERM or $signo === SIGINT or $signo === SIGHUP){
			$this->shutdown();
		}
	}

	public function checkTicks(){
		if($this->getTicksPerSecond() < 12){
			$this->logger->warning("Can't keep up! Is the server overloaded?");
		}
	}

	public function exceptionHandler(\Throwable $e, $trace = null){
		if($e === null){
			return;
		}

		global $lastError;

		if($trace === null){
			$trace = $e->getTrace();
		}

		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errno = $e->getCode();
		$errline = $e->getLine();

		$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? \LogLevel::ERROR : (($errno === E_USER_WARNING or $errno === E_WARNING) ? \LogLevel::WARNING : \LogLevel::NOTICE);
		if(($pos = strpos($errstr, "\n")) !== false){
			$errstr = substr($errstr, 0, $pos);
		}

		$errfile = cleanPath($errfile);

		if($this->logger instanceof MainLogger){
			$this->logger->logException($e, $trace);
		}

		$lastError = [
			"type" => $type,
			"message" => $errstr,
			"fullFile" => $e->getFile(),
			"file" => $errfile,
			"line" => $errline,
			"trace" => @getTrace(1, $trace)
		];

		global $lastExceptionError, $lastError;
		$lastExceptionError = $lastError;
		$this->crashDump();
	}

	/**
	 * @return void
	 */
	public function crashDump(){
		if($this->isRunning === false){
			return;
		}
		$this->isRunning = false;
		$this->hasStopped = false;

		ini_set("error_reporting", 0);
		ini_set("memory_limit", -1); //Fix error dump not dumped on memory problems
		$this->logger->emergency("An unrecoverable error has occurred and the server has crashed. Creating a crash dump");
		try{
			$dump = new CrashDump($this);
		}catch(\Exception $e){
			$this->logger->critical("Could not create Crash Dump: " . $e->getMessage());
			return;
		}

		$this->logger->emergency("Please submit the \"" . $dump->getPath() . "\" file to the Bug Reporting page. Give as much info as you can.");


		if($this->getProperty("auto-report.enabled", true) !== false){
			$report = true;
			$plugin = $dump->getData()["plugin"];
			if(is_string($plugin)){
				$p = $this->pluginManager->getPlugin($plugin);
				if($p instanceof Plugin and !($p->getPluginLoader() instanceof PharPluginLoader)){
					$report = false;
				}
			} elseif(\Phar::running(true) == ""){
				$report = false;
			}
			if($dump->getData()["error"]["type"] === "E_PARSE" or $dump->getData()["error"]["type"] === "E_COMPILE_ERROR"){
				$report = false;
			}

			if($report){
				$reply = Utils::postURL("http://" . $this->getProperty("auto-report.host", "crash.pocketmine.net") . "/submit/api", [
					"report" => "yes",
					"name" => $this->getName() . " " . $this->getPocketMineVersion(),
					"email" => "crash@pocketmine.net",
					"reportPaste" => base64_encode($dump->getEncodedData())
				]);

				if(($data = json_decode($reply)) !== false and isset($data->crashId)){
					$reportId = $data->crashId;
					$reportUrl = $data->crashUrl;
					$this->logger->emergency("The crash dump has been automatically submitted to the Crash Archive. You can view it on $reportUrl or use the ID #$reportId.");
				}
			}
		}

		//$this->checkMemory();
		//$dump .= "Memory Usage Tracking: \r\n" . chunk_split(base64_encode(gzdeflate(implode(";", $this->memoryStats), 9))) . "\r\n";

		$this->forceShutdown();
		@kill(getmypid());
		exit(1);
	}

	public function __debugInfo(){
		return [];
	}


		private function tickProcessor(){
		$this->nextTick = microtime(true);
		while($this->isRunning){
			$this->tick();
			$next = $this->nextTick - 0.0001;
			if($next > microtime(true)){
				try{
					@time_sleep_until($next);
				}catch(\Throwable $e){
					//Sometimes $next is less than the current time. High load?
				}
			}
		}
	}

	public function addOnlinePlayer(Player $player){
		$this->playerList[$player->getRawUniqueId()] = $player;		
	}

	public function removeOnlinePlayer(Player $player) {
		if (isset($this->playerList[$player->getRawUniqueId()])) {
			unset($this->playerList[$player->getRawUniqueId()]);
			$this->removePlayerListData($player->getUniqueId(), $this->playerList);
		}
	}

	public function updatePlayerListData(UUID $uuid, $entityId, $name, $skinName, $skinData, $skinGeometryName, $skinGeometryData, $capeData, $xuid, $players){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries[] = [$uuid, $entityId, $name, $skinName, $skinData, $capeData, $skinGeometryName, $skinGeometryData, $xuid];
		 
		$readyPackets = [];
		foreach ($players as $p){
			$protocol = $p->getPlayerProtocol();
			if (!isset($readyPackets[$protocol])) {
				$pk->setDeviceId($p->getDeviceOS());
				$pk->encode($protocol, $p->getSubClientId());
				$batch = new BatchPacket();
				$batch->payload = zlib_encode(Binary::writeVarInt(strlen($pk->getBuffer())) . $pk->getBuffer(), Player::getCompressAlg($protocol), 7);
				$readyPackets[$protocol] = $batch;
			}
			$p->dataPacket($readyPackets[$protocol]);
		}
	}

	public function removePlayerListData(UUID $uuid, $players){
		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries[] = [$uuid];
		Server::broadcastPacket($players, $pk);
	}

	private $craftList = [];
	
	public function sendRecipeList(Player $p){
		if(!isset($this->craftList[$p->getPlayerProtocol()])) {
			$pk = new CraftingDataPacket();
			$pk->cleanRecipes = true;
			
			$recipies = [];
			
			foreach($this->getCraftingManager()->getRecipes() as $recipe){
				$recipies[] = $recipe;
			}
			foreach($this->getCraftingManager()->getFurnaceRecipes() as $recipe){
				$recipies[] = $recipe;
			}
			
			$this->getPluginManager()->callEvent($ev = new SendRecipiesList($recipies));
			
			foreach($ev->getRecipies() as $recipe){
				if($recipe instanceof ShapedRecipe){
					$pk->addShapedRecipe($recipe);
				} elseif($recipe instanceof ShapelessRecipe){
					$pk->addShapelessRecipe($recipe);
				} elseif($recipe instanceof FurnaceRecipe) {
					$pk->addFurnaceRecipe($recipe);
				}
			}
			
			$pk->encode($p->getPlayerProtocol(), $p->getSubClientId());
			$bpk = new BatchPacket();
			$buffer = $pk->getBuffer();
			var_dump("PROTOCOL: " . $p->getPlayerProtocol());
			if ($p->getPlayerProtocol() >= Info::PROTOCOL_406) {
				$bpk->payload = zlib_encode(Binary::writeVarInt(strlen($buffer)) . $buffer, ZLIB_ENCODING_RAW, 7);
			} else {
				$bpk->payload = zlib_encode(Binary::writeVarInt(strlen($buffer)) . $buffer, ZLIB_ENCODING_DEFLATE, 7);
			}
			$bpk->encode($p->getPlayerProtocol());
			$this->craftList[$p->getPlayerProtocol()] = $bpk->getBuffer();
		}
		$p->getInterface()->putReadyPacket($p, $this->craftList[$p->getPlayerProtocol()]);
	}

	public function addPlayer($identifier, Player $player){
		$this->players[$identifier] = $player;
		$this->identifiers[spl_object_hash($player)] = $identifier;
	}

	private function checkTickUpdates($currentTick){

		//Do level ticks
		foreach($this->getLevels() as $level){
			try{
				$level->doTick($currentTick);
			}catch(\Exception $e){
				$this->logger->critical("Could not tick level " . $level->getName() . ": " . $e->getMessage());
				if(\pocketmine\DEBUG > 1 and $this->logger instanceof MainLogger){
					$this->logger->logException($e);
				}
			}
		}
		foreach ($this->players as $player) {
			$player->sendPacketQueue();
		}
	}

	public function doAutoSave(){
		if($this->getSavePlayerData()){
			foreach($this->getOnlinePlayers() as $index => $player){
				if($player->isOnline()){
					$player->save();
				} elseif(!$player->isConnected()){
					$this->removePlayer($player);
				}
			}
		}
		if($this->getAutoSave()){
			foreach($this->getLevels() as $level){
				$level->save(false);
			}
		}
	}

	public function doLevelGC(){
		foreach($this->getLevels() as $level){
			$level->doChunkGarbageCollection();
		}
	}

	/**
	 * @return Network
	 */
	public function getNetwork(){
		return $this->network;
	}

	private function titleTick(){
		if(defined("pocketmine\\DEBUG") and \pocketmine\DEBUG >= 0 and \pocketmine\ANSI === true){
			echo "\x1b]0;" . $this->getName() . " " . $this->getPocketMineVersion() . " | Online " . count($this->players) . "/" . $this->getMaxPlayers() . " | RAM " . round((memory_get_usage() / 1024) / 1024, 2) . "/" . round((memory_get_usage(true) / 1024) / 1024, 2) . " MB | U " . round($this->network->getUpload() / 1024, 2) . " D " . round($this->network->getDownload() / 1024, 2) . " kB/s | TPS " . $this->getTicksPerSecond() . " | Load " . $this->getTickUsage() . "%\x07";
		}
	}
	
	/**
	 * @param string $address
	 * @param int    $port
	 * @param string $payload
	 *
	 * TODO: move this to Network
	 */
	public function handlePacket($address, $port, $payload){
		try{
			if(strlen($payload) > 2 and substr($payload, 0, 2) === "\xfe\xfd" and $this->queryHandler instanceof QueryHandler){
				$this->queryHandler->handle($address, $port, $payload);
			}
		}catch(\Exception $e){
			if(\pocketmine\DEBUG > 1){
				if($this->logger instanceof MainLogger){
					$this->logger->logException($e);
				}
			}

			$this->getNetwork()->blockAddress($address, 600);
		}
		//TODO: add raw packet events
	}


	/**
	 * Tries to execute a server tick
	 */
	private function tick(){
		$tickTime = microtime(true);
		if($tickTime < $this->nextTick){
			return false;
		}
		
		//TimingsHandler::reload();

		//Timings::$serverTickTimer->startTiming();

		++$this->tickCounter;
		
		$this->checkConsole();
		
		foreach ($this->unloadLevelQueue as $levelForUnload) {
			$this->unloadLevel($levelForUnload['level'], $levelForUnload['force'], true);
		}
		$this->unloadLevelQueue = [];
	
		//Timings::$connectionTimer->startTiming();
		$this->network->processInterfaces();
		if($this->rcon !== null){
			$this->rcon->check();
		}
		//Timings::$connectionTimer->stopTiming();

		//Timings::$schedulerTimer->startTiming();
		$this->scheduler->mainThreadHeartbeat($this->tickCounter);
		//Timings::$schedulerTimer->stopTiming();

		$this->checkTickUpdates($this->tickCounter);
		
		if(($this->tickCounter & 0b1111) === 0){
			$this->titleTick();
			if($this->queryHandler !== null and ($this->tickCounter & 0b111111111) === 0){
				try{
					$this->queryHandler->regenerateInfo();
				}catch(\Exception $e){
					if($this->logger instanceof MainLogger){
						$this->logger->logException($e);
					}
				}
			}
		}

		if(($this->tickCounter % 100) === 0){
			foreach($this->levels as $level){
				$level->clearCache();
			}
		}
		

		if ($this->tickCounter % 200 === 0 && ($this->isUseAnimal() || $this->isUseMonster())) {
			SpawnerCreature::generateEntity($this, $this->isUseAnimal(), $this->isUseMonster());
		}
		//Timings::$serverTickTimer->stopTiming();

		//TimingsHandler::tick();

		$now = microtime(true);
		array_shift($this->tickAverage);
		$tickDiff = $now - $tickTime;
		$this->tickAverage[] = ($tickDiff <= 0.05) ? 20 : 1 / $tickDiff;
		array_shift($this->useAverage);
		$this->useAverage[] = min(1, $tickDiff * 20);

		if(($this->nextTick - $tickTime) < -1){
			$this->nextTick = $tickTime;
		}
		$this->nextTick += 0.05;

//		if(microtime(true) - $tickTime > 0.06){
//			$timingFolder = $this->getDataPath() . "timings/";
//
//			if(!file_exists($timingFolder)){
//				mkdir($timingFolder, 0777);
//			}
//			$timings = $timingFolder . "timings.txt";
//			TimingsHandler::printTimings($timings);
//		}
		return true;
	}

	private function registerEntities(){
		Entity::registerEntity(Minecart::class);
		Entity::registerEntity(Boat::class);
		Entity::registerEntity(FishingHook::class);
		Entity::registerEntity(Arrow::class);
		Entity::registerEntity(SplashPotion::class);
		Entity::registerEntity(DroppedItem::class);
		Entity::registerEntity(FallingSand::class);
		Entity::registerEntity(PrimedTNT::class);
		Entity::registerEntity(Snowball::class);
		Entity::registerEntity(Egg::class);
		Entity::registerEntity(Villager::class);
		Entity::registerEntity(Squid::class);
		Entity::registerEntity(Human::class, true);		
		
		Entity::registerEntity(Blaze::class);
		Entity::registerEntity(CaveSpider::class);
		Entity::registerEntity(Chicken::class);
		Entity::registerEntity(Cow::class);
		Entity::registerEntity(Creeper::class);
		Entity::registerEntity(Enderman::class);
		Entity::registerEntity(Ghast::class);
		Entity::registerEntity(IronGolem::class);
		Entity::registerEntity(Mooshroom::class);
		Entity::registerEntity(Ocelot::class);
		Entity::registerEntity(Pig::class);
		Entity::registerEntity(PigZombie::class);
		Entity::registerEntity(Rabbit::class);
		Entity::registerEntity(Sheep::class);
		Entity::registerEntity(Silverfish::class);
		Entity::registerEntity(Skeleton::class);
		Entity::registerEntity(SnowGolem::class);
		Entity::registerEntity(Spider::class);
		Entity::registerEntity(Wolf::class);
		Entity::registerEntity(Zombie::class);
		Entity::registerEntity(ZombieVillager::class);
		Entity::registerEntity(FireBall::class);
		Entity::registerEntity(BottleOEnchanting::class);
		Entity::registerEntity(ExperienceOrb::class);

		Entity::registerEntity(Painting::class);
	}

	private function registerTiles(){
		Tile::registerTile(Chest::class);
		Tile::registerTile(Furnace::class);
		Tile::registerTile(Sign::class);
		Tile::registerTile(EnchantTable::class);
		Tile::registerTile(Skull::class);
		Tile::registerTile(FlowerPot::class);
		Tile::registerTile(EnderChest::class);
		Tile::registerTile(Bed::class);
		Tile::registerTile(Cauldron::class);
		Tile::registerTile(Dispenser::class);
		Tile::registerTile(PistonArm::class);
		Tile::registerTile(ShulkerBox::class);
		Tile::registerTile(ItemFrame::class);
		Tile::registerTile(Dropper::class);
		Tile::registerTile(Hopper::class);
		Tile::registerTile(Beacon::class);
		Tile::registerTile(Banner::class);
	}

	public function shufflePlayers(){
		if(count($this->players) <= 1){
			return;
		}

		$keys = array_keys($this->players);
		shuffle($keys);
		$random = [];
		foreach ($keys as $key) {
			$random[$key] = $this->players[$key];
		}

		$this->players = $random;
	}
	
	public function getJsonCommands() {
		return $this->jsonCommands;
	}
	
	public function isUseEncrypt() {
		return $this->isUseEncrypt;
	}
		
	public function getServerPublicKey() {
		return $this->serverPublicKey;
	}
	
	public function getServerPrivateKey() {
		return $this->serverPrivateKey;
	}
	
	public function getServerToken() {	
		return $this->serverToken;
	}
	
	public function addLevel($level) {
		$this->levels[$level->getId()] = $level;
	}

	public function test() {
		$a = '9101ec09010a0200000000020a820400000000030a820800000000040a820c00000000050a821000000000060a82140000000007e303020000000008e503020000000009960202000000000a96028204000000000b96028208000000000c9602820c000000000d96028210000000000e96028214000000000f9602823000000000109602821c00000000119602822000000000129602821800000000139602822400000000149602823400000000159602822800000000169602822c0000000017a904020000000018d104020000000019ab0402000000001aaa0102000000001baa018204000000001caa018208000000001daa01820c000000001eaa018210000000001faa0182140000000020e201020000000021ff030200000000228104020000000023d601020000000024ee02020000000025f002020000000026f202020000000027f602020000000028f402020000000029830402000000002a850402000000002be70202000000002c860102000000002de50202000000002e6a02000000002f8c020200000000308e020200000000319002020000000032c602020000000033c802020000000034da01020000000035dd020200000000368002020000000037e102020000000038e802020000000039df0202000000003ad10202000000003bd70202000000003cd30202000000003dd90202000000003ed50202000000003fdb02020000000040d801020000000041e401020000000042ef02020000000043e302020000000044b802020000000045f102020000000046960302000000004703020000000048050200000000490702000000004afb0302000000004bfd0302000000004ca70402000000004dc70402000000004ea50402000000004f8805020000000050d606020000000051d806020000000052da06020000000053dc06020000000054de060200000000559405020000000056e60b020000000057e80b020000000058c001020000000059a90202000000005aa30202000000005ba70202000000005ca10202000000005da50202000000005ece0202000000005feb03020000000060ed03020000000061ca01020000000062ec0b02000000006328020000000064e203020000000065e20382200000000066e203821c0000000067e203823c0000000068e20382300000000069e2038238000000006ae2038204000000006be2038210000000006ce2038214000000006de2038234000000006ee2038224000000006fe203820c0000000070e203822c0000000071e20382280000000072e20382080000000073e20382180000000074cc01020000000075c002020000000076c00282200000000077c002821c0000000078c002823c0000000079c0028230000000007ac0028238000000007bc0028204000000007cc0028210000000007dc0028214000000007ec0028234000000007fc0028224000000008001c002820c000000008101c002822c000000008201c0028228000000008301c0028208000000008401c0028218000000008501820102000000008601c902020000000087015802000000008801cb02820800000000890158820c000000008a01ec028214000000008b01bc0202000000008c01bc028204000000008d01bc028208000000008e01bc02820c000000008f01bc028210000000009001bc028214000000009101588214000000009201cb0202000000009301588204000000009401cb02820c000000009501ec028218000000009601ec0202000000009701cb028210000000009801c3028204000000009901c3028218000000009a01c302821c000000009b01c3028210000000009c01c3028214000000009d01c302820c000000009e01c3028208000000009f0158821000000000a00158821c00000000a101ec02821c00000000a201c3020200000000a30158821800000000a401cb02820400000000a501ec02820400000000a601ec02820800000000a701ec02820c00000000a801ec02821000000000a9018f040200000000aa0191040200000000ab01b3040200000000ac01c9040200000000ad01b7040200000000ae015a0200000000af01db040200000000b001dd040200000000b101df040200000000b201c4010200000000b301c401820400000000b401c401820800000000b501c401820c00000000b6019c030200000000b701d002820800000000b801a3040200000000b901af040200000000ba01b1040200000000bb01ad040200000000bc01080200000000bd01600200000000be01ed020200000000bf01300200000000c00130820400000000c10130820800000000c20130820c00000000c301e6020200000000c401e602820400000000c501e602820800000000c601e602820c00000000c701da020200000000c80195020200000000c901520200000000ca01540200000000cb018a020200000000cc01720200000000cd012c0200000000ce01b6020200000000cf01b602820800000000d001b602820400000000d101b602820c00000000d201d0020200000000d301d002820400000000d401ca020200000000d501b7030200000000d601b9030200000000d701d4020200000000d801dd030200000000d901b0030200000000da01ac030200000000db01c5030200000000dc01e0010200000000dd01ae030200000000de01c1030200000000df01c3030200000000e001df030200000000e101e1030200000000e201d5040200000000e301d3040200000000e401d7040200000000e501d9040200000000e601cb030200000000e701cf030200000000e801d1030200000000e901d3030200000000ea01d5030200000000eb01d7030200000000ec019b040200000000ed01bb030200000000ee01460200000000ef0146822000000000f00146821c00000000f10146823c00000000f20146823000000000f30146823800000000f40146820400000000f50146821000000000f60146821400000000f70146823400000000f80146822400000000f90146820c00000000fa0146822c00000000fb0146822800000000fc0146820800000000fd0146821800000000fe01d6020200000000ff01d6028220000000008002d602821c000000008102d602823c000000008202d6028230000000008302d6028238000000008402d6028204000000008502d6028210000000008602d6028214000000008702d6028234000000008802d6028224000000008902d602820c000000008a02d602822c000000008b02d6028228000000008c02d6028208000000008d02d6028218000000008e02da0302000000008f02da038220000000009002da03821c000000009102da03823c000000009202da038230000000009302da038238000000009402da038204000000009502da038210000000009602da038214000000009702da038234000000009802da038224000000009902da03820c000000009a02da03822c000000009b02da038228000000009c02da038208000000009d02da038218000000009e02d80302000000009f02d803822000000000a002d803821c00000000a102d803823c00000000a202d803823000000000a302d803823800000000a402d803820400000000a502d803821000000000a602d803821400000000a702d803823400000000a802d803822400000000a902d803820c00000000aa02d803822c00000000ab02d803822800000000ac02d803820800000000ad02d803821800000000ae02a4010200000000af02d8020200000000b002be020200000000b102be02822000000000b202be02821c00000000b302be02823c00000000b402be02823000000000b502be02823800000000b602be02820400000000b702be02821000000000b802be02821400000000b902be02823400000000ba02be02822400000000bb02be02820c00000000bc02be02822c00000000bd02be02822800000000be02be02820800000000bf02be02821800000000c002b8030200000000c102c8030200000000c202c6030200000000c302d6030200000000c402d0030200000000c502d4030200000000c602ba030200000000c702c0030200000000c802c2030200000000c902d2030200000000ca02ca030200000000cb02be030200000000cc02ce030200000000cd02b6030200000000ce02bc030200000000cf02c4030200000000d00292030200000000d1029203820800000000d202060200000000d30206820400000000d402040200000000d5028c030200000000d602e6030200000000d702dc010200000000d802020200000000d9021e0200000000da021c0200000000db02700200000000dc022a0200000000dd0292010200000000de02200200000000df0282020200000000e002b2020200000000e102bf040200000000e2029d040200000000e3021a0200000000e40202820400000000e50202820c00000000e60202821400000000e702a1040200000000e80202820800000000e90202821000000000ea0202821800000000eb02c5040200000000ec02180200000000ed0218820400000000ee02a2010200000000ef02220200000000f002130200000000f10222820400000000f202090200000000f30222820800000000f4020b0200000000f50222820c00000000f6020d0200000000f702c4020200000000f8020f0200000000f902c402820400000000fa02110200000000fb02a7030200000000fc02a703822000000000fd02a703820400000000fe02a703822400000000ff02a7038208000000008003a7038228000000008103a703820c000000008203a703822c000000008303a7038210000000008403a7038230000000008503a7038214000000008603a70382340000000087032402000000008803248204000000008903248208000000008a0324820c000000008b03c20202000000008c03c2028204000000008d030c02000000008e030c8204000000008f030c82080000000090030c820c0000000091030c82100000000092030c8214000000009303b303820c000000009403ce0402000000009503d20502000000009603d40502000000009703940702000000009803d00402000000009903920702000000009a03900602000000009b03940602000000009c038e0602000000009d03980602000000009e03880402000000009f0384050200000000a003a4070200000000a103ce010200000000a203d0050200000000a303fc050200000000a403ba070200000000a503ac010200000000a603b5020200000000a703b6010200000000a803c00b0200000000a9033e820800000000aa03de02820c00000000ab033e820400000000ac03de02820800000000ad03f00b0200000000ae038502820c00000000af038502820400000000b0038502820800000000b10385020200000000b2038502821000000000b3038502822c00000000b4038502822400000000b5038502822800000000b6038502822000000000b7038502823000000000b8038902820c00000000b9038902820400000000ba038902820800000000bb0389020200000000bc038902821000000000bd038b02820c00000000be038b02820400000000bf038b02820800000000c0038b020200000000c1038b02821000000000c2039e050200000000c30383020200000000c403bd030200000000c503bf030200000000c6034a0200000000c7034c0200000000c8034c820400000000c9034c820800000000ca034c820c00000000cb034c821000000000cc034c821400000000cd034c821800000000ce034c821c00000000cf034c822000000000d0034c822400000000d1034c822800000000d203de020200000000d303de02820400000000d403de02821000000000d503de02821400000000d603af030200000000d703be05824c00000000d803be05821c00000000d903be05822000000000da03be05824000000000db03be05824400000000dc03be05820400000000dd03be05823800000000de03be05822c00000000df03be05822800000000e003be05820800000000e103be05821800000000e203be05823000000000e303be05824800000000e403be05821400000000e503be05823400000000e603be05822400000000e703be050200000000e803be05820c00000000e903be05821000000000ea03be05823c00000000eb03d4010200000000ec03cd030200000000ed03bd040200000000ee03de010200000000ef03400200000000f003c5020200000000f103a0010200000000f2039e010200000000f303dc020200000000f403150200000000f5039c010200000000f603da050200000000f703fe040200000000f803d6050200000000f903ce060200000000fa03b6060200000000fb03ba050200000000fc0398070200000000fd039a070200000000fe039c070200000000ff034e020000000080045002000000008104c70302000000008204c90302000000008304c6018238000000008404c8018238000000008504c601823c000000008604c60102000000008704b00502000000008804a40502000000008904c20502000000008a04de0502000000008b04c00502000000008c043c02000000008d04ee0502000000008e046802000000008f04c20102000000009004c2018204000000009104c2018208000000009204c201820c000000009304c2018210000000009404c2018214000000009504f40102000000009604bd0202000000009704fe058228000000009804fe0582e803000000009904fe05822c000000009a04fe058230000000009b04fe058234000000009c04fe058238000000009d04fe058270000000009e04fe058258000000009f04fe0582ac0200000000a004fe05824000000000a104fe05824c00000000a204fe05827800000000a304fe05824800000000a404fe05827400000000a504fe05825c00000000a604fe05826000000000a704fe05826400000000a804fe05826800000000a904fe05826c00000000aa04fe0582bc0300000000ab04fe0582c00300000000ac04fe0582b00300000000ad04fe0582b40300000000ae04fe05827c00000000af04fe0582a80200000000b004fe0582c40300000000b104fe0582e40300000000b204fe0582840100000000b304fe0582980100000000b404fe05829c0100000000b504fe0582880100000000b604fe0582c00100000000b704fe0582b80100000000b804fe0582940100000000b904fe05828c0100000000ba04fe0582800100000000bb04fe0582900100000000bc04fe0582bc0100000000bd04fe0582b80300000000be04fe05824400000000bf04fe0582a00100000000c004fe0582b40100000000c104fe0582c40100000000c204fe0582c80100000000c304fe0582dc0100000000c404fe0582a80100000000c504fe0582f40300000000c604fe0582f00300000000c704fe0582ec0300000000c804fe0582f80300000000c904fe0582a40100000000ca04fe0582ac0100000000cb04fe0582d80100000000cc04fe0582e40100000000cd04fe0582a00300000000ce04fe0582a40300000000cf04fe0582cc0300000000d004fe0582d80300000000d104fe0582d00300000000d204fe0582e80100000000d304fe0582c80300000000d404fe0582ec0100000000d504620200000000d604c1040200000000d7040e0200000000d804b0010200000000d904ae010200000000da04aa030200000000db04e8050200000000dc04f2010200000000dd0490030200000000de04e0030200000000df04e0060200000000e004e2060200000000e104260200000000e20426820400000000e30487020200000000e4048702820400000000e5048702820800000000e6048702820c00000000e7048702821000000000e8048702822000000000e9048702822400000000ea048702822800000000eb048702822c00000000ec048702823000000000ed04d4040200000000ee04dc040200000000ef04e4040200000000f004f4040200000000f104ec040200000000f204d80b0200000000f304d6040200000000f404de040200000000f504e6040200000000f604f6040200000000f704ee040200000000f804da0b0200000000f904d8040200000000fa04e0040200000000fb04e8040200000000fc04f8040200000000fd04f0040200000000fe04dc0b0200000000ff04da0402000000008005e20402000000008105ea0402000000008205fa0402000000008305f20402000000008405de0b02000000008505980402000000008605a00402000000008705960402000000008805b60402000000008905a80402000000008a05ce0b02000000008b059e0402000000008c05a60402000000008d05840402000000008e05bc0402000000008f05ae0402000000009005d40b020000000091059c0402000000009205a40402000000009305820402000000009405ba0402000000009505ac0402000000009605d20b020000000097059a0402000000009805a20402000000009905800402000000009a05b80402000000009b05aa0402000000009c05d00b02000000009d05c40402000000009e05c60402000000009f05c8040200000000a005cc040200000000a105ca040200000000a205d60b0200000000a3058a040200000000a405ae070200000000a5058c040200000000a6058c04821800000000a7058c04821c00000000a8058c04822000000000a9058c04822400000000aa058c04822800000000ab058c04822c00000000ac058c04823000000000ad058c04823400000000ae058c04823800000000af058c04823c00000000b0058c04824000000000b1058c04824400000000b2058c04824800000000b3058c04824c00000000b4058c04825000000000b5058c04825400000000b6058c04825800000000b7058c04825c00000000b8058c04826000000000b9058c04826400000000ba058c04826800000000bb058c04826c00000000bc058c04827000000000bd058c04827400000000be058c04827800000000bf058c04827c00000000c0058c0482800100000000c1058c0482840100000000c2058c0482880100000000c3058c04828c0100000000c4058c0482900100000000c5058c0482940100000000c6058c0482980100000000c7058c04829c0100000000c8058c0482a00100000000c9058c0482a40100000000ca058c0482a80100000000cb058c0482ac0100000000cc058208020000000000cd05dc050200000000ce0580050200000000cf05d8050200000000d005d0060200000000d105b8060200000000d205bc050200000000d3059e070200000000d405d2040200000000d505b4040200000000d60596070200000000d705ba060200000000d80592060200000000d905ca050200000000da05a0060200000000db05c4050200000000dc05a0070200000000dd05b4050200000000de059c060200000000df05ea0b0200000000e00598050200000000e105ce050200000000e20586040200000000e305c8060200000000e405b6050200000000e505b2050200000000e60596060200000000e7059606820800000000e80592050200000000e905c0060200000000ea05c2060200000000eb05c4060200000000ec05c6060200000000ed058e070200000000ee05aa070200000000ef05f8060200000000f00584070200000000f105ec050200000000f20580060200000000f305ea050200000000f405ea05820400000000f505ea05820800000000f605ea05820c00000000f705ea05821000000000f805ea05821400000000f905ea05821800000000fa05ea05821c00000000fb05ea05822000000000fc05ea05822400000000fd05ea05822800000000fe05ea05822c00000000ff05ea058230000000008006ea058234000000008106ea058238000000008206ea05823c000000008306ea058240000000008406ea058244000000008506ea058248000000008606ea05824c000000008706ea058250000000008806ea058254000000008906ea058258000000008a06ea05825c000000008b06ea058260000000008c06ea058264000000008d06ea058268000000008e06ea05826c000000008f06ea058270000000009006ea058274000000009106ea058278000000009206ea05827c000000009306ea05828001000000009406ea05828401000000009506ea05828801000000009606ea05828c01000000009706ea05829001000000009806ea05829401000000009906ea05829801000000009a06ea05829c01000000009b06ea0582a001000000009c06ea0582a401000000009d06ea0582a801000000009e06ec0602000000009f06ec06820400000000a006ec06820800000000a106ec06820c00000000a206ec06821000000000a306ec06821400000000a406ec06821800000000a506ec06821c00000000a606ec06822000000000a706ec06822400000000a806ec06822800000000a906ec06822c00000000aa06ec06823000000000ab06ec06823400000000ac06ec06823800000000ad06ec06823c00000000ae06ec06824000000000af06ec06824400000000b006ec06824800000000b106ec06824c00000000b206ec06825000000000b306ec06825400000000b406ec06825800000000b506ec06825c00000000b606ec06826000000000b706ec06826400000000b806ec06826800000000b906ec06826c00000000ba06ec06827000000000bb06ec06827400000000bc06ec06827800000000bd06ec06827c00000000be06ec0682800100000000bf06ec0682840100000000c006ec0682880100000000c106ec06828c0100000000c206ec0682900100000000c306ec0682940100000000c406ec0682980100000000c506ec06829c0100000000c606ec0682a00100000000c706ec0682a40100000000c806ec0682a80100000000c906f2060200000000ca06f206820400000000cb06f206820800000000cc06f206820c00000000cd06f206821000000000ce06f206821400000000cf06f206821800000000d006f206821c00000000d106f206822000000000d206f206822400000000d306f206822800000000d406f206822c00000000d506f206823000000000d606f206823400000000d706f206823800000000d806f206823c00000000d906f206824000000000da06f206824400000000db06f206824800000000dc06f206824c00000000dd06f206825000000000de06f206825400000000df06f206825800000000e006f206825c00000000e106f206826000000000e206f206826400000000e306f206826800000000e406f206826c00000000e506f206827000000000e606f206827400000000e706f206827800000000e806f206827c00000000e906f20682800100000000ea06f20682840100000000eb06f20682880100000000ec06f206828c0100000000ed06f20682900100000000ee06f20682940100000000ef06f20682980100000000f006f206829c0100000000f106f20682a00100000000f206f20682a40100000000f306f20682a80100000000f406b0040200000000f506c6050200000000f606c605822000000000f706c605821c00000000f806c605823c00000000f906c605823000000000fa06c605823800000000fb06c605820400000000fc06c605821000000000fd06c605821400000000fe06c605823400000000ff06c6058224000000008007c605820c000000008107c605822c000000008207c6058228000000008307c6058208000000008407c60582180000000085076402000000008607970402000000008707b702020000000088079f0302000000008907990402000000008a077402000000008b078f0302000000008c07910302000000008d07930302000000008e07b503820c000000008f07a00b02000000009007c20c020000000091077a020000000092078703020000000093078b03020000000094079f0402000000009507f60502000000009607a20202000000009707a2028210000000009807a2028220000000009907850302000000009a07e80102000000009b075e02000000009c07830302000000009d07f80502000000009e07a90302000000009f076c0200000000a007a4020200000000a10784020200000000a20795030200000000a3079a030200000000a407b4030200000000a507b403822000000000a607b403821c00000000a707b403823c00000000a807b403823000000000a907b403823800000000aa07b403820400000000ab07b403821000000000ac07b403821400000000ad07b403823400000000ae07b403822400000000af07b403820c00000000b007b403822c00000000b107b403822800000000b207b403820800000000b307b403821800000000b407d2060200000000b507320200000000b607a8010200000000b707e8070200000000b807ea070200000000b907ec070200000000ba07ee070200000000bb07f0070200000000bc07f2070200000000bd07f4070200000000be07f6070200000000bf07f8070200000000c007fa070200000000c107fc070200000000c207fe070200000000c307ee0b0200000000c407b8050200000000c507b2010200000000c607f6010200000000c707d2020200000000c80786050200000000c907b0070200000000ca07b2070200000000cb07b4070200000000cc07b6070200000000cd07b8070200000000ce07e20b0200000000cf07e40b0200000000d00782050200000000d1078a060200000000d207c20b0200000000d3078c060200000000d407b2040200000000d5078a050200000000d6078a05820400000000d7078a05822000000000d8078a05822800000000d9078a05820800000000da078a05820c00000000db078a05821000000000dc078a05821400000000dd079a06820c00000000de079a06820800000000df079a06821000000000e0079a06821400000000e1079a060200000000e2079a06820400000000e30794020200000000e4079b030200000000e507b9020200000000e60789030200000000e707f0010200000000e8078e040200000000e9078e04820400000000ea0790040200000000eb0788070200000000ec0792040200000000ed07e00b0200000000ee07cc0b0200000000ef07e6050200000000f00794040200000000f10788060200000000f207ac060200000000f307a2050200000000f407a0050200000000f507aa060200000000f607b2060200000000f707cc060200000000f807a2070200000000f907a6070200000000fa07a8070200000000fb07ac070200000000fc07be040200000000fd07c0040200000000fe07fc040200000000ff07c204020000000080089c0502000000008108be0602000000008208bc0602000000008308820602000000008408e20502000000008508f20502000000008608f40502000000008708f00502000000008808ea0602000000008908fa0602000000008a08e40502000000008b08aa0502000000008c08e00502000000008d08fa0502000000008e089e0602000000008f08a00302000000009008d40602000000009108a60502000000009208a80502000000009308840602000000009408a60602ffff010a000904656e63680a0202026964000002036c766c0100000000009508a60602ffff010a000904656e63680a0202026964000002036c766c0200000000009608a60602ffff010a000904656e63680a0202026964000002036c766c0300000000009708a60602ffff010a000904656e63680a0202026964000002036c766c0400000000009808a60602ffff010a000904656e63680a0202026964010002036c766c0100000000009908a60602ffff010a000904656e63680a0202026964010002036c766c0200000000009a08a60602ffff010a000904656e63680a0202026964010002036c766c0300000000009b08a60602ffff010a000904656e63680a0202026964010002036c766c0400000000009c08a60602ffff010a000904656e63680a0202026964020002036c766c0100000000009d08a60602ffff010a000904656e63680a0202026964020002036c766c0200000000009e08a60602ffff010a000904656e63680a0202026964020002036c766c0300000000009f08a60602ffff010a000904656e63680a0202026964020002036c766c040000000000a008a60602ffff010a000904656e63680a0202026964030002036c766c010000000000a108a60602ffff010a000904656e63680a0202026964030002036c766c020000000000a208a60602ffff010a000904656e63680a0202026964030002036c766c030000000000a308a60602ffff010a000904656e63680a0202026964030002036c766c040000000000a408a60602ffff010a000904656e63680a0202026964040002036c766c010000000000a508a60602ffff010a000904656e63680a0202026964040002036c766c020000000000a608a60602ffff010a000904656e63680a0202026964040002036c766c030000000000a708a60602ffff010a000904656e63680a0202026964040002036c766c040000000000a808a60602ffff010a000904656e63680a0202026964050002036c766c010000000000a908a60602ffff010a000904656e63680a0202026964050002036c766c020000000000aa08a60602ffff010a000904656e63680a0202026964050002036c766c030000000000ab08a60602ffff010a000904656e63680a0202026964060002036c766c010000000000ac08a60602ffff010a000904656e63680a0202026964060002036c766c020000000000ad08a60602ffff010a000904656e63680a0202026964060002036c766c030000000000ae08a60602ffff010a000904656e63680a0202026964070002036c766c010000000000af08a60602ffff010a000904656e63680a0202026964070002036c766c020000000000b008a60602ffff010a000904656e63680a0202026964070002036c766c030000000000b108a60602ffff010a000904656e63680a0202026964080002036c766c010000000000b208a60602ffff010a000904656e63680a0202026964090002036c766c010000000000b308a60602ffff010a000904656e63680a0202026964090002036c766c020000000000b408a60602ffff010a000904656e63680a0202026964090002036c766c030000000000b508a60602ffff010a000904656e63680a0202026964090002036c766c040000000000b608a60602ffff010a000904656e63680a0202026964090002036c766c050000000000b708a60602ffff010a000904656e63680a02020269640a0002036c766c010000000000b808a60602ffff010a000904656e63680a02020269640a0002036c766c020000000000b908a60602ffff010a000904656e63680a02020269640a0002036c766c030000000000ba08a60602ffff010a000904656e63680a02020269640a0002036c766c040000000000bb08a60602ffff010a000904656e63680a02020269640a0002036c766c050000000000bc08a60602ffff010a000904656e63680a02020269640b0002036c766c010000000000bd08a60602ffff010a000904656e63680a02020269640b0002036c766c020000000000be08a60602ffff010a000904656e63680a02020269640b0002036c766c030000000000bf08a60602ffff010a000904656e63680a02020269640b0002036c766c040000000000c008a60602ffff010a000904656e63680a02020269640b0002036c766c050000000000c108a60602ffff010a000904656e63680a02020269640c0002036c766c010000000000c208a60602ffff010a000904656e63680a02020269640c0002036c766c020000000000c308a60602ffff010a000904656e63680a02020269640d0002036c766c010000000000c408a60602ffff010a000904656e63680a02020269640d0002036c766c020000000000c508a60602ffff010a000904656e63680a02020269640e0002036c766c010000000000c608a60602ffff010a000904656e63680a02020269640e0002036c766c020000000000c708a60602ffff010a000904656e63680a02020269640e0002036c766c030000000000c808a60602ffff010a000904656e63680a02020269640f0002036c766c010000000000c908a60602ffff010a000904656e63680a02020269640f0002036c766c020000000000ca08a60602ffff010a000904656e63680a02020269640f0002036c766c030000000000cb08a60602ffff010a000904656e63680a02020269640f0002036c766c040000000000cc08a60602ffff010a000904656e63680a02020269640f0002036c766c050000000000cd08a60602ffff010a000904656e63680a0202026964100002036c766c010000000000ce08a60602ffff010a000904656e63680a0202026964110002036c766c010000000000cf08a60602ffff010a000904656e63680a0202026964110002036c766c020000000000d008a60602ffff010a000904656e63680a0202026964110002036c766c030000000000d108a60602ffff010a000904656e63680a0202026964120002036c766c010000000000d208a60602ffff010a000904656e63680a0202026964120002036c766c020000000000d308a60602ffff010a000904656e63680a0202026964120002036c766c030000000000d408a60602ffff010a000904656e63680a0202026964130002036c766c010000000000d508a60602ffff010a000904656e63680a0202026964130002036c766c020000000000d608a60602ffff010a000904656e63680a0202026964130002036c766c030000000000d708a60602ffff010a000904656e63680a0202026964130002036c766c040000000000d808a60602ffff010a000904656e63680a0202026964130002036c766c050000000000d908a60602ffff010a000904656e63680a0202026964140002036c766c010000000000da08a60602ffff010a000904656e63680a0202026964140002036c766c020000000000db08a60602ffff010a000904656e63680a0202026964150002036c766c010000000000dc08a60602ffff010a000904656e63680a0202026964160002036c766c010000000000dd08a60602ffff010a000904656e63680a0202026964170002036c766c010000000000de08a60602ffff010a000904656e63680a0202026964170002036c766c020000000000df08a60602ffff010a000904656e63680a0202026964170002036c766c030000000000e008a60602ffff010a000904656e63680a0202026964180002036c766c010000000000e108a60602ffff010a000904656e63680a0202026964180002036c766c020000000000e208a60602ffff010a000904656e63680a0202026964180002036c766c030000000000e308a60602ffff010a000904656e63680a0202026964190002036c766c010000000000e408a60602ffff010a000904656e63680a0202026964190002036c766c020000000000e508a60602ffff010a000904656e63680a02020269641a0002036c766c010000000000e608a60602ffff010a000904656e63680a02020269641b0002036c766c010000000000e708a60602ffff010a000904656e63680a02020269641c0002036c766c010000000000e808a60602ffff010a000904656e63680a02020269641d0002036c766c010000000000e908a60602ffff010a000904656e63680a02020269641d0002036c766c020000000000ea08a60602ffff010a000904656e63680a02020269641d0002036c766c030000000000eb08a60602ffff010a000904656e63680a02020269641d0002036c766c040000000000ec08a60602ffff010a000904656e63680a02020269641d0002036c766c050000000000ed08a60602ffff010a000904656e63680a02020269641e0002036c766c010000000000ee08a60602ffff010a000904656e63680a02020269641e0002036c766c020000000000ef08a60602ffff010a000904656e63680a02020269641e0002036c766c030000000000f008a60602ffff010a000904656e63680a02020269641f0002036c766c010000000000f108a60602ffff010a000904656e63680a02020269641f0002036c766c020000000000f208a60602ffff010a000904656e63680a02020269641f0002036c766c030000000000f308a60602ffff010a000904656e63680a0202026964200002036c766c010000000000f408a60602ffff010a000904656e63680a0202026964210002036c766c010000000000f508a60602ffff010a000904656e63680a0202026964220002036c766c010000000000f608a60602ffff010a000904656e63680a0202026964220002036c766c020000000000f708a60602ffff010a000904656e63680a0202026964220002036c766c030000000000f808a60602ffff010a000904656e63680a0202026964220002036c766c040000000000f908a60602ffff010a000904656e63680a0202026964230002036c766c010000000000fa08a60602ffff010a000904656e63680a0202026964230002036c766c020000000000fb08a60602ffff010a000904656e63680a0202026964230002036c766c030000000000fc08a60602ffff010a000904656e63680a0202026964240002036c766c010000000000fd08a60602ffff010a000904656e63680a0202026964240002036c766c020000000000fe08a60602ffff010a000904656e63680a0202026964240002036c766c030000000000ff089a05020000000080099a0582040000000081099a0582080000000082099a05820c0000000083099a0582100000000084099a05821400000000850984010200000000860936020000000087093802000000008809fc0102000000008909900502000000008a09ac0502000000008b09b00602000000008c09ae0602000000008d09960502000000008e09b00202000000008f099801020000000090098a01020000000091099e02020000000092099f02020000000093099902020000000094099d02020000000095099702020000000096099b02020000000097099a0102000000009809870402000000009909890402000000009a09cf0402000000009b09860202000000009c09900102000000009d09b30202000000009e09ad0202000000009f09b1020200000000a009ab020200000000a109af020200000000a2098b040200000000a3098d040200000000a4098c010200000000a509a6020200000000a609a8020200000000a709cd040200000000a809f6030200000000a909ae020200000000aa09c8050200000000ab09a8060200000000ac09b4060200000000ad09fa01820c00000000ae092e820c00000000af0942820400000000b0093a820400000000b1095c0200000000b209ca060200000000b30997030200000000b409fc060200000000b509fc06822000000000b609fc06821c00000000b709fc06823c00000000b809fc06823000000000b909fc06823800000000ba09fc06820400000000bb09fc06821000000000bc09fc06821400000000bd09fc06823400000000be09fc06822400000000bf09fc06820c00000000c009fc06822c00000000c109fc06822800000000c209fc06820800000000c309fc06821800000000c409fc06823cffff010a0003045479706502000000c509e4060200000000c609e406820400000000c709e406820800000000c809e406820c00000000c909e406821000000000ca09e406821400000000cb09e406821800000000cc09a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e7300000106466c696768740100000000cd09a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720200070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000ce09a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720208070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000cf09a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720207070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d009a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f72020f070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d109a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f72020c070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d209a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f72020e070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d309a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720201070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d409a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720204070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d509a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720205070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d609a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f72020d070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d709a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720209070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d809a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720203070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000d909a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f72020b070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000da09a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f72020a070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000db09a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720202070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000dc09a20602ffff010a000a0946697265776f726b73090a4578706c6f73696f6e730a02070d46697265776f726b436f6c6f720206070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b5479706500000106466c696768740100000000dd09a40602ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720200070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72bd8b970e000000de09a4068220ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720208070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72dbc2c50b000000df09a406821cffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720207070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72d1899306000000e009a406823cffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f72020f070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f729fbc78000000e109a4068230ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f72020c070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72cbb0aa0c000000e209a4068238ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f72020e070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72c5ff33000000e309a4068204ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720201070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72b3c7fe04000000e409a4068210ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720204070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72abed9d0c000000e509a4068214ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720205070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f728fb5b607000000e609a4068234ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f72020d070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f7285c5c503000000e709a4068224ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720209070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72abd163000000e809a406820cffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720203070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f729bafe507000000e909a406822cffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f72020b070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72859f09000000ea09a4068228ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f72020a070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72c1e3f907000000eb09a4068208ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720202070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72d38f8c0a000000ec09a4068218ffff010a000a0d46697265776f726b734974656d070d46697265776f726b436f6c6f720206070c46697265776f726b4661646500010f46697265776f726b466c69636b657200010d46697265776f726b547261696c00010c46697265776f726b547970650000030b637573746f6d436f6c6f72c78dcb0e000000';

		$b = new \pocketmine\utils\BinaryStream(hex2bin($a), 1);
		$count = $b->getVarInt();
		var_dump('array size: ' .  $count);

		for ($i = 0; $i < $count; $i++) {
			var_dump('array size: ' .  $b->getVarInt());
			var_dump('Item id: ' .  $b->getVarInt());
			var_dump('Aux Value: ' .  $b->getVarInt());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			$item = $b->getSlot(Info::PROTOCOL_407);
			var_dump($item->getName());
			var_dump($b->get(true));
		}
		var_dump($b->get(true));
		die;
	}

}

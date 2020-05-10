<?php
namespace pocketmine\entity;

use pocketmine\Player;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\level\Level;
use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\Compound;
use pocketmine\math\Vector3;

class FishingHook extends Entity {

	const NETWORK_ID = 77;

	public $width = 0.25;
	public $length = 0.25;
	public $height = 0.25;
	protected $gravity = 0.1;
	protected $drag = 0.05;

	public $data = 0;
	public $attractTimer = 100;
	public $coughtTimer = 0;
	public $damageRod = false;

	public function initEntity(){
		parent::initEntity();

		if(isset($this->namedtag->Data)){
			$this->data = $this->namedtag["Data"];
		}

		// $this->setDataProperty(FallingSand::DATA_BLOCK_INFO, self::DATA_TYPE_INT, $this->getData());
	}

	public function __construct(FullChunk $chunk, CompoundTag $nbt, Entity $shootingEntity = null){
		parent::__construct($chunk, $nbt, $shootingEntity);
	}

	public function setData($id){
		$this->data = $id;
	}

	public function getData(){
		return $this->data;
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		//$this->timings->startTiming();

		$hasUpdate = parent::onUpdate($currentTick);

		if($this->isCollided && $this->isInsideOfWater()){
			$this->motionX = 0;
			$this->motionY += 0.01;
			$this->motionZ = 0;
			$this->motionChanged = true;
			$hasUpdate = true;
		}
		if($this->attractTimer === 0 && mt_rand(0, 100) <= 30){ // chance, that a fish bites
			$this->coughtTimer = mt_rand(5, 10) * 20; // random delay to catch fish
			$this->attractTimer = mt_rand(30, 100) * 20; // reset timer
			$this->attractFish();
			if($this->shootingEntity instanceof Player) $this->shootingEntity->sendTip("A fish bites!");
		}elseif($this->attractTimer > 0){
			$this->attractTimer--;
		}
		if($this->coughtTimer > 0){
			$this->coughtTimer--;
			$this->fishBites();
		}

		//$this->timings->stopTiming();

		return $hasUpdate;
	}

	public function fishBites(){
		if($this->shootingEntity instanceof Player){
			$pk = new EntityEventPacket();
			$pk->eid = $this->shootingEntity->getId();//$this or $this->shootingEntity
			$pk->event = EntityEventPacket::FISH_HOOK_HOOK;
			Server::broadcastPacket($this->shootingEntity->hasSpawned, $pk);
		}
	}

	public function attractFish(){
		if($this->shootingEntity instanceof Player){
			$pk = new EntityEventPacket();
			$pk->eid = $this->shootingEntity->getId();//$this or $this->shootingEntity
			$pk->event = EntityEventPacket::FISH_HOOK_BUBBLE;
			Server::broadcastPacket($this->shootingEntity->hasSpawned, $pk);
		}
	}

	public function reelLine(){
		$this->damageRod = false;

		if($this->shootingEntity instanceof Player && $this->coughtTimer > 0){
			$fishes = [ItemItem::RAW_FISH, ItemItem::RAW_SALMON, ItemItem::CLOWN_FISH, ItemItem::PUFFER_FISH];
			$fish = array_rand($fishes, 1);
			$item = ItemItem::get($fishes[$fish]);
			$this->getLevel()->getServer()->getPluginManager()->callEvent($ev = new PlayerFishEvent($this->shootingEntity, $item, $this));
			if(!$ev->isCancelled()){
				$this->shootingEntity->getInventory()->addItem($item);
				$this->shootingEntity->addExperience(mt_rand(1, 6));
				$this->damageRod = true;
			}
		}
	}

	public function spawnTo(Player $player) {
		if (!isset($this->hasSpawned[$player->getId()]) && isset($player->usedChunks[Level::chunkHash($this->chunk->getX(), $this->chunk->getZ())])) {
			$this->hasSpawned[$player->getId()] = $player;
			$pk = new AddEntityPacket();
			$pk->eid = $this->getId();
			$pk->type = FishingHook::NETWORK_ID;
			$pk->x = $this->x;
			$pk->y = $this->y;
			$pk->z = $this->z;
			$pk->speedX = $this->motionX;
			$pk->speedY = $this->motionY;
			$pk->speedZ = $this->motionZ;
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
//			$pk->metadata = $this->dataProperties;
			$player->dataPacket($pk);
		}
	}

	/*public function onUpdate($currentTick) {
		if ($this->closed !== false) {
			return false;
		}

		if ($this->dead === true) {
			$this->removeAllEffects();
			$this->despawnFromAll();
			$this->close();
			return false;
		}
		$tickDiff = $currentTick - $this->lastUpdate;
		if ($tickDiff < 1) {
			return true;
		}

		$this->lastUpdate = $currentTick;
		$hasUpdate = $this->entityBaseTick($tickDiff);
		if ($this->isAlive()) {
			if (!$this->onGround) {
				$this->motionY -= $this->gravity;
				$this->move($this->motionX, $this->motionY, $this->motionZ);
				$this->updateMovement();
			}
		}
		return $hasUpdate || $this->motionX != 0 || $this->motionY != 0 || $this->motionZ != 0;
	}*/

	public function move($dx, $dy, $dz) {
		if ($dx == 0 && $dz == 0 && $dy == 0) {
			return true;
		}
		$this->boundingBox->offset($dx, $dy, $dz);
		$block = $this->level->getBlock(new Vector3($this->x, $this->y + $dy, $this->z));
		if ($dy < 0 && !$block->isTransparent()) {
			$newY = (int) $this->y;
			for ($tempY = (int) $this->y; $tempY > (int) ($this->y + $dy); $tempY--) {
				$block = $this->level->getBlock(new Vector3($this->x, $tempY, $this->z));
				if ($block->isTransparent()) {
					$newY = $tempY;
				}
			}
			$this->onGround = true;
			$this->motionY = 0;
			$this->motionX = 0;
			$this->motionZ = 0;
			$addY = $this->boundingBox->maxY - $this->boundingBox->minY - 1;
			$this->setComponents($this->x + $dx, $newY + $addY, $this->z + $dz);
		} else {
			$this->setComponents($this->x + $dx, $this->y + $dy, $this->z + $dz);
		}
	}

}

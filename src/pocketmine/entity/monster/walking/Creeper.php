<?php

namespace pocketmine\entity\monster\walking;

use pocketmine\entity\monster\WalkingMonster;
use pocketmine\entity\Creature;
use pocketmine\entity\Entity;
use pocketmine\entity\Explosive;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\level\Explosion;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\IntTag;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Item;

class Creeper extends WalkingMonster implements Explosive{
	const NETWORK_ID = 33;

	public $width = 0.72;
	public $height = 1.8;

	private $bombTime = 0;

	public function getSpeed(){
		return 0.9;
	}

	public function initEntity(){
		parent::initEntity();

		if(isset($this->namedtag->BombTime)){
			$this->bombTime = (int) $this->namedtag["BombTime"];
		}
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->BombTime = new IntTag("BombTime", $this->bombTime);
	}

	public function getName(){
		return "Creeper";
	}

	public function explode(){
		$this->server->getPluginManager()->callEvent($ev = new ExplosionPrimeEvent($this, 2.8));

		if(!$ev->isCancelled()){
			$explosion = new Explosion($this, $ev->getForce(), $this);
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
			$this->close();
		}
	}

	public function attackEntity(Entity $player){
	    if($player instanceof Creature){
	        $x = $player->x - $this->x;
	        $y = $player->y - $this->y;
	        $z = $player->z - $this->z;
	        
	        $target = $player;
	        $distance = sqrt(pow($this->x - $target->x, 2) + pow($this->z - $target->z, 2));
	        if($distance <= 4.5){
	            if($target instanceof Creature){
	                $this->bombTime += 1;
	                if($this->bombTime >= 64){
	                    $this->explode();
	                    return false;
	                }
	            }else if(pow($this->x - $target->x, 2) + pow($this->z - $target->z, 2) <= 1){
	                $this->moveTime = 0;
	            }
	        }else{
	            $this->bombTime -= 1;
	            if($this->bombTime < 0){
	                $this->bombTime = 0;
	            }
	            
	            $diff = abs($x) + abs($z);
	            $this->motionX = $this->getSpeed() * 0.15 * ($x / $diff);
	            $this->motionZ = $this->getSpeed() * 0.15 * ($z / $diff);
	        }
	        $this->yaw = rad2deg(-atan2($this->motionX, $this->motionZ));
	        $this->pitch = $y == 0 ? 0 : rad2deg(-atan2($y, sqrt($x * $x + $z * $z)));
	    }
	}

	public function getDrops(){
		if($this->lastDamageCause instanceof EntityDamageByEntityEvent){
			switch(mt_rand(0, 2)){
				case 0:
					return [Item::get(Item::FLINT, 0, 1)];
				case 1:
					return [Item::get(Item::GUNPOWDER, 0, 1)];
				case 2:
					return [Item::get(Item::REDSTONE_DUST, 0, 1)];
			}
		}
		return [];
	}

}
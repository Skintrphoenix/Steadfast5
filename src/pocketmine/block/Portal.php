<?php

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Position;
use pocketmine\Server;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\math\Vector3;

class Portal extends Transparent {
    
    protected $id = self::PORTAL;
	
	public function __construct() {
		
	}
    
    public function getName() {
        return 'Portal';
    }
    
    public function isBreakable(Item $item) {
        return false;
    }
    
    public function getDrops(Item $item) {
        return [];
    }
    
    public function hasEntityCollision(){
        return true;
    }
    
    public function onEntityCollide(Entity $entity){
        $level = $this->getLevel();
        if($level->getProvider()->getGenerator() === "nether") {
            $entity->teleport(new Position($entity->getX(), Server::getInstance()->getDefaultLevel()->getHighestBlockAt($entity->getX(), $entity->getZ()), $entity->getZ(), Server::getInstance()->getDefaultLevel()));
        } else {
            Server::getInstance()->getNetherLevel()->setBlock(new Vector3(Server::getInstance()->getNetherLevel()->getSpawnLocation()->getX(), 80, Server::getInstance()->getNetherLevel()->getSpawnLocation()->getZ()), Block::get(Block::OBSIDIAN), true);
            $entity->teleport(new Position(Server::getInstance()->getNetherLevel()->getSpawnLocation()->getX(), 81, Server::getInstance()->getNetherLevel()->getSpawnLocation()->getZ(), Server::getInstance()->getNetherLevel()));
        }
    }
}

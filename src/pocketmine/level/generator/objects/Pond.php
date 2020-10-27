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

namespace pocketmine\level\generator\objects;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\math\Vector3;

class Pond{
	private $random;
	public $type;
	const TO_NOT_OVERWRITE = [
	    Block::WATER,
	    Block::STILL_WATER,
	    Block::STILL_LAVA,
	    Block::LAVA,
	    Block::BEDROCK,
	    Block::CACTUS,
	    Block::PLANKS];

	public function __construct(Random $random, Block $type){
		$this->type = $type;
		$this->random = $random;
	}

	public function canPlaceObject(ChunkManager $level, $x, $y, $z){
	    return true;
	}

	public function placeObject(ChunkManager $level, $x, $y, $z){
	    $x = $this->random->nextRange($x, $x + 15);
	    $z = $this->random->nextRange($z, $z + 15);
	    $ory = $this->random->nextRange(20, 63); // Water level
	    $y = $ory;
	    for($i = 0; $i < 4; $i ++) {
	        $x += $this->random->nextRange(- 1, 1);
	        $y += $this->random->nextRange(- 1, 1);
	        $z += $this->random->nextRange(- 1, 1);
	        if ($level->getBlockIdAt($x, $y, $z) !== Block::AIR)
	            self::buildRandom($level, new Vector3($x, $y, $z), new Vector3(5, 5, 5), $this->random, $this->type);
	    }
	    for($xx = $x - 8; $xx <= $x + 8; $xx ++)
	        for($zz = $z - 8; $zz <= $z + 8; $zz ++)
	            for($yy = $ory + 1; $yy <= $y + 3; $yy ++)
	                if ($level->getBlockIdAt($xx, $yy, $zz) == Block::WATER || $level->getBlockIdAt($xx, $yy, $zz) == Block::LAVA)
	                    $level->setBlockIdAt($xx, $yy, $zz, Block::AIR);
	}
	
	private static function buildRandom(ChunkManager $level, Vector3 $pos, Vector3 $infos, Random $random, Block $block) {
	    $xBounded = $random->nextBoundedInt(3) - 1;
	    $yBounded = $random->nextBoundedInt(3) - 1;
	    $zBounded = $random->nextBoundedInt(3) - 1;
	    $pos = $pos->round();
	    for($x = $pos->x -($infos->x / 2); $x <= $pos->x +($infos->x / 2); $x++) {
	        for($y = $pos->y -($infos->y / 2); $y <= $pos->y +($infos->y / 2); $y++) {
	            for($z = $pos->z -($infos->z / 2); $z <= $pos->z +($infos->z / 2); $z++) {
	                // if(abs((abs($x) - abs($pos->x)) ** 2 +($y - $pos->y) ** 2 +(abs($z) - abs($pos->z)) ** 2) <(abs($infos->x / 2 + $xBounded) + abs($infos->y / 2 + $yBounded) + abs($infos->z / 2 + $zBounded)) ** 2
	                if(abs((abs($x) - abs($pos->x)) ** 2 +($y - $pos->y) ** 2 +(abs($z) - abs($pos->z)) ** 2) <((($infos->x / 2 - $xBounded) +($infos->y / 2 - $yBounded) +($infos->z / 2 - $zBounded)) / 3) ** 2 && $y > 0 && ! in_array($level->getBlockIdAt($x, $y, $z), self::TO_NOT_OVERWRITE) && ! in_array($level->getBlockIdAt($x, $y + 1, $z), self::TO_NOT_OVERWRITE)) {
	                    $level->setBlockIdAt($x, $y, $z, $block->getId());
	                    $level->setBlockDataAt($x, $y, $z, $block->getDamage());
	                }
	            }
	        }
	    }
	}

}
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

namespace pocketmine\level\generator\populator;

use pocketmine\block\Water;
use pocketmine\block\Lava;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\block\Block;

class Pond extends Populator{
	private $waterOdd = 4;
	private $lavaOdd = 4;
	private $lavaSurfaceOdd = 4;

	public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random){
		if($random->nextRange(0, $this->waterOdd) === 0){
			$x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 16);
			$z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 16);
			$y = $this->getHighestWorkableBlock($level, $x, $z);
			$pond = new \pocketmine\level\generator\objects\Pond($random, new Water());
			if($random->nextRange(0, $this->lavaOdd) === 0){
			    $pond = new \pocketmine\level\generator\objects\Pond($random, new Lava());
			}
			if($pond->canPlaceObject($level, $x, $y, $z)){
				$pond->placeObject($level, $x, $y, $z);
			}
		}
	}

	public function setWaterOdd($waterOdd){
		$this->waterOdd = $waterOdd;
	}

	public function setLavaOdd($lavaOdd){
		$this->lavaOdd = $lavaOdd;
	}

	public function setLavaSurfaceOdd($lavaSurfaceOdd){
		$this->lavaSurfaceOdd = $lavaSurfaceOdd;
	}
	
	/**
	 * Gets the top block (y) on an x and z axes
	 * @param int $x
	 * @param int $z
	 */
	protected function getHighestWorkableBlock($level, $x, $z) {
	    for($y = $level->getMaxY() - 1; $y > 0; -- $y) {
	        $b = $level->getBlockIdAt($x, $y, $z);
	        if ($b === Block::DIRT or $b === Block::GRASS or $b === Block::PODZOL) {
	            break;
	        } elseif ($b !== 0 and $b !== Block::SNOW_LAYER) {
	            return - 1;
	        }
	    }
	    
	    return ++$y;
	}
}
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

class SugarCane extends PopulatorObject {
    protected $totalHeight;
    
    /**
     * Checks if a sugarcane is placable
     *
     * @param ChunkManager $level
     * @param int $x
     * @param int $y
     * @param int $z
     * @param Random $random
     * @return bool
     */
    public function canPlaceObject(ChunkManager $level, int $x, int $y, int $z, Random $random): bool {
        $this->totalHeight = 1 + $random->nextBoundedInt(3);
        $below = $level->getBlockIdAt($x, $y - 1, $z);
        if (($below == Block::SAND || $below == Block::GRASS) && ($level->getBlockIdAt($x + 1, $y - 1, $z) == Block::WATER || $level->getBlockIdAt($x - 1, $y - 1, $z) == Block::WATER || $level->getBlockIdAt($x, $y - 1, $z + 1) == Block::WATER || $level->getBlockIdAt($x, $y - 1, $z - 1) == Block::WATER)) {
            return true;
        }
        return false;
    }
    
    /**
     * Places a sugar cane
     *
     * @param ChunkManager $level
     * @param int $x
     * @param int $y
     * @param int $z
     * @return void
     */
    public function placeObject(ChunkManager $level, int $x, int $y, int $z) {
        for($yy = 0; $yy < $this->totalHeight; $yy ++) {
            if ($level->getBlockIdAt($x, $y + $yy, $z) != Block::AIR) {
                return;
            }
            $level->setBlockIdAt($x, $y + $yy, $z, Block::SUGARCANE_BLOCK);
        }
    }
}

?>
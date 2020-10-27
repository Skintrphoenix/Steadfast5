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

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\biome\Biome;
use pocketmine\utils\Random;
use pocketmine\level\generator\objects\SugarCane as SugarCaneObj;

class SugarCane extends Populator {
    protected $baseAmount = 0;
    protected $randomAmount = 0;
    
    /**
     * Sets the random addition amount
     * @param $amount int
     */
    public function setRandomAmount(int $amount) {
        $this->randomAmount = $amount;
    }
    
    /**
     * Sets the base addition amount
     * @param $amount int
     */
    public function setBaseAmount(int $amount) {
        $this->baseAmount = $amount;
    }
    
    /**
     * Returns the amount based on random
     *
     * @param Random $random
     * @return int
     */
    public function getAmount(Random $random) {
        return $this->baseAmount + $random->nextRange(0, $this->randomAmount + 1);
    }
    
    /**
     * Returns base amount
     *
     * @return int
     */
    public function getBaseAmount(): int {
        return $this->baseAmount;
    }
    
    /**
     * Returns the random additional amount
     *
     * @return int
     */
    public function getRandomAmount(): int {
        return $this->randomAmount;
    }
    
    protected $level;
    
    /**
     * Populates the chunk
     *
     * @param ChunkManager $level
     * @param int $chunkX
     * @param int $chunkZ
     * @param Random $random
     * @return void
     */
    public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random) {
        $this->level = $level;
        $amount = $this->getAmount($random);
        $sugarcane = new SugarCaneObj();
        for($i = 0; $i < $amount; $i++) {
            $x = $random->nextRange($chunkX * 16, $chunkX * 16 + 15);
            $z = $random->nextRange($chunkZ * 16, $chunkZ * 16 + 15);
            $y = $this->getHighestWorkableBlock($x, $z);
            if ($y !== -1 and $sugarcane->canPlaceObject($level, $x, $y, $z, $random)) {
                $sugarcane->placeObject($level, $x, $y, $z);
            }
        }
    }
    
    /**
     * Gets the top block (y) on an x and z axes
     * @param $x int
     * @param $z int
     * @return int
     */
    protected function getHighestWorkableBlock($x, $z) {
        for($y = $this->level->getMaxY() - 1; $y >= 0; -- $y) {
            $b = $this->level->getBlockIdAt($x, $y, $z);
            if ($b !== Block::AIR and $b !== Block::LEAVES and $b !== Block::LEAVES2) {
                break;
            }
        }
        return $y === 0 ? - 1 : ++$y;
    }
}

?>
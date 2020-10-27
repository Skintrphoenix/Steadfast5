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
use pocketmine\level\Level;
use pocketmine\math\Vector3;

class Ravine extends Populator{
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
    const NOISE = 250;
    
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
        if ($amount > 50) { // Only build one per chunk
            $depth = $random->nextBoundedInt(60) + 30; // 2Much4U?
            $x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
            $z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
            $y = $random->nextRange(5, $this->getHighestWorkableBlock($x, $z));
            $deffX = $x;
            $deffZ = $z;
            $height = $random->nextRange(15, 30);
            $length = $random->nextRange(5, 12);
            for($i = 0; $i < $depth; $i ++) {
                $this->buildRavinePart($x, $y, $z, $height, $length, $random);
                $diffX = $x - $deffX;
                $diffZ = $z - $deffZ;
                if ($diffX > $length / 2)
                    $diffX = $length / 2;
                    if ($diffX < - $length / 2)
                        $diffX = - $length / 2;
                        if ($diffZ > $length / 2)
                            $diffZ = $length / 2;
                            if ($diffZ < - $length / 2)
                                $diffZ = - $length / 2;
                                if ($length > 10)
                                    $length = 10;
                                    if ($length < 5)
                                        $length = 5;
                                        $x += $random->nextRange(0 + $diffX, 2 + $diffX) - 1;
                                        $y += $random->nextRange(0, 2) - 1;
                                        $z += $random->nextRange(0 + $diffZ, 2 + $diffZ) - 1;
                                        $height += $random->nextRange(0, 2) - 1;
                                        $length += $random->nextRange(0, 2) - 1;
            }
        }
    }
    
    /*
     * Gets the top block (y) on an x and z axes
     * @param $x int
     * @param $z int
     */
    protected function getHighestWorkableBlock($x, $z) {
        for($y = $this->level->getMaxY() - 1; $y > 0; -- $y) {
            $b = $this->level->getBlockIdAt($x, $y, $z);
            if ($b === Block::DIRT or $b === Block::GRASS or $b === Block::PODZOL or $b === Block::SAND or $b === Block::SNOW_BLOCK or $b === Block::SANDSTONE) {
                break;
            } elseif ($b !== 0 and $b !== Block::SNOW_LAYER and $b !== Block::WATER) {
                return - 1;
            }
        }
        
        return ++$y;
    }
    
    /**
     * Buidls a ravine part
     *
     * @param int $x
     * @param int $y
     * @param int $z
     * @param int $height
     * @param int $length
     * @param Random $random
     * @return void
     */
    protected function buildRavinePart($x, $y, $z, $height, $length, Random $random) {
        $xBounded = 0;
        $zBounded = 0;
        for($xx = $x - $length; $xx <= $x + $length; $xx ++) {
            for($yy = $y; $yy <= $y + $height; $yy ++) {
                for($zz = $z - $length; $zz <= $z + $length; $zz ++) {
                    $oldXB = $xBounded;
                    $xBounded = $random->nextBoundedInt(self::NOISE * 2) - self::NOISE;
                    $oldZB = $zBounded;
                    $zBounded = $random->nextBoundedInt(self::NOISE * 2) - self::NOISE;
                    if ($xBounded > self::NOISE - 2) {
                        $xBounded = 1;
                    } elseif ($xBounded < - self::NOISE + 2) {
                        $xBounded = -1;
                    } else {
                        $xBounded = $oldXB;
                    }
                    if ($zBounded > self::NOISE - 2) {
                        $zBounded = 1;
                    } elseif ($zBounded < - self::NOISE + 2) {
                        $zBounded = -1;
                    } else {
                        $zBounded = $oldZB;
                    }
                    if (abs((abs($xx) - abs($x)) ** 2 + (abs($zz) - abs($z)) ** 2) < ((($length / 2 - $xBounded) + ($length / 2 - $zBounded)) / 2) ** 2 && $y > 0 && ! in_array($this->level->getBlockIdAt(( int) round($xx),(int) round($yy),(int) round($zz)), Cave::TO_NOT_OVERWRITE) && ! in_array($this->level->getBlockIdAt(( int) round($xx),(int) round($yy + 1),(int) round($zz)), Cave::TO_NOT_OVERWRITE)) {
                        $this->level->setBlockIdAt(( int) round($xx),(int) round($yy),(int) round($zz), Block::AIR);
                    }
                }
            }
        }
    }
}

?>
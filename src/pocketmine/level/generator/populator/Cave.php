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

class Cave extends Populator{
    protected $baseAmount = 0;
    protected $randomAmount = 0;
    
    const TO_NOT_OVERWRITE = [
        Block::WATER,
        Block::STILL_WATER,
        Block::STILL_LAVA,
        Block::LAVA,
        Block::BEDROCK,
        Block::CACTUS,
        Block::GOLD_ORE,
        Block::IRON_ORE,
        Block::COAL_ORE,
        Block::LAPIS_ORE,
        Block::DIAMOND_ORE,
        Block::REDSTONE_ORE,
        Block::PLANKS];
    
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
    const STOP = false;
    const CONTINUE = true;
    
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
        for($i = 0; $i < $amount; $i++) {
            $x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
            $z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
            $y = $random->nextRange(10, $this->getHighestWorkableBlock($x, $z));
            // echo "Generating cave at $x, $y, $z." . PHP_EOL;
            $this->generateCave($x, $y, $z, $random);
        }
        // echo "Finished Populating chunk $chunkX, $chunkZ !" . PHP_EOL;
        // Filling water & lava sources randomly
        for($i = 0; $i < $random->nextBoundedInt(5) + 3; $i ++) {
            $x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
            $z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
            $y = $random->nextRange(10, $this->getHighestWorkableBlock($x, $z));
            if ($level->getBlockIdAt($x, $y, $z) == Block::STONE && ($level->getBlockIdAt($x + 1, $y, $z) == Block::AIR || $level->getBlockIdAt($x - 1, $y, $z) == Block::AIR || $level->getBlockIdAt($x, $y, $z + 1) == Block::AIR || $level->getBlockIdAt($x, $y, $z - 1) == Block::AIR) && $level->getBlockIdAt($x, $y - 1, $z) !== Block::AIR && $level->getBlockIdAt($x, $y + 1, $z) !== Block::AIR) {
                if ($y < 40 && $random->nextBoolean ()) {
                    $level->setBlockIdAt($x, $y, $z, Block::LAVA);
                } else {
                    $level->setBlockIdAt($x, $y, $z, Block::WATER);
                }
            }
        }
    }
    
    /**
     * Gets the top block (y) on an x and z axes
     * @param int $x
     * @param int $z
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
     * Generates a cave
     *
     * @param int $x
     * @param int $y
     * @param int $z
     * @param Random $random
     * @return void
     */
    public function generateCave($x, $y, $z, Random $random) {
        $generatedBranches = $random->nextBoundedInt(10) + 1;
        foreach($gen = $this->generateBranch($x, $y, $z, 5, 3, 5, $random) as $v3) {
            $generatedBranches --;
            if ($generatedBranches <= 0) {
                $gen->send(self::STOP);
            } else {
                $gen->send(self::CONTINUE);
            }
        }
    }
    
    /**
     * Generates a cave branch
     *
     * @param int $x
     * @param int $y
     * @param int $z
     * @param int $length
     * @param int $height
     * @param int $depth
     * @param Random $random
     * @yield Vector3
     * @return void
     */
    public function generateBranch($x, $y, $z, $length, $height, $depth, Random $random) {
        if (! (yield new Vector3($x, $y, $z))) {
            for($i = 0; $i <= 4; $i ++) {
                self::buildRandom($this->level, new Vector3($x, $y, $z), new Vector3($length - $i, $height - $i, $depth - $i), $random, Block::get(Block::AIR));
                $x += round(($random->nextBoundedInt(round(30 * ($length / 10)) + 1) / 10 - 2));
                $yP = $random->nextRange(-14, 14);
                if ($yP > 12) {
                    $y ++;
                } elseif ($yP < - 12) {
                    $y --;
                }
                $z += round(($random->nextBoundedInt(round(30 * ($depth / 10)) + 1) / 10 - 1));
                return;
            }
        }
        $repeat = $random->nextBoundedInt(25) + 15;
        while($repeat-- > 0) {
            self::buildRandom($this->level, new Vector3($x, $y, $z), new Vector3($length, $height, $depth), $random, Block::get(Block::AIR));
            $x += round(($random->nextBoundedInt(round(30 * ($length / 10)) + 1) / 10 - 2));
            $yP = $random->nextRange(- 14, 14);
            if ($yP > 12) {
                $y ++;
            } elseif ($yP < - 12) {
                $y --;
            }
            $z += round(($random->nextBoundedInt(round(30 * ($depth / 10)) + 1) / 10 - 1));
            $height += $random->nextBoundedInt(3) - 1;
            $length += $random->nextBoundedInt(3) - 1;
            $depth += $random->nextBoundedInt(3) - 1;
            if ($height < 3)
                $height = 3;
                if ($length < 3)
                    $length = 3;
                    if ($height < 3)
                        $height = 3;
                        if ($height < 7)
                            $height = 7;
                            if ($length < 7)
                                $length = 7;
                                if ($height < 7)
                                    $height = 7;
                                    if ($random->nextBoundedInt(10) == 0) {
                                        foreach($generator = $this->generateBranch($x, $y, $z, $length, $height, $depth, $random) as $gen) {
                                            if (!(yield $gen))
                                                $generator->send(self::STOP);
                                        }
                                    }
        }
        return;
    }
    
    public static function buildRandom(ChunkManager $level, Vector3 $pos, Vector3 $infos, Random $random, Block $block) {
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

?>
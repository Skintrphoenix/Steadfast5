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
use pocketmine\block\Wood;
use pocketmine\math\Vector3;

class Bush extends PopulatorObject{
    public $overridable = [
        Block::AIR => true,
        17 => true,
        Block::SNOW_LAYER => true,
        Block::LOG2 => true
    ];
    protected $leaf;
    /** @var int */
    protected $height;
    
    /**
     * Constructs the class
     *
     * @param int $leafId
     * @param int $leafData
     */
    public function __construct($type = Wood::OAK) {
        $this->leaf = $type;
    }
    
    /**
     * Places a bush
     *
     * @param ChunkManager $level
     * @param int $x
     * @param int $y
     * @param int $z
     * @param Random $random
     * @return void
     */
    public function placeObject(ChunkManager $level, $x, $y, $z, Random $random) {
        $number = $random->nextBoundedInt(6);
        $pos = new Vector3($x, $y, $z);
        $this->placeLeaf($pos->x, $pos->y, $pos->z, $level);
        for($i = 0; $i < $number; $i ++) {
            $transfer = $random->nextBoolean ();
            $direction = $random->nextBoundedInt(6);
            $newPos = $pos->getSide($direction);
            if ($transfer)
                $pos = $newPos;
                $this->placeLeaf($newPos->x, $newPos->y, $newPos->z, $level);
        }
    }
    
    /**
     * Places a leaf
     *
     * @param int $x
     * @param int $y
     * @param int $z
     * @param ChunkManager $level
     * @return void
     */
    public function placeLeaf($x, $y, $z, ChunkManager $level) {
        if (isset($this->overridable[$level->getBlockIdAt($x, $y, $z)]) && ! isset($this->overridable[$level->getBlockIdAt($x, $y - 1, $z)])) {
            $level->setBlockIdAt($x, $y, $z, Block::LEAVES);
            $level->setBlockDataAt($x, $y, $z, $this->leaf);
        }
    }
}

?>
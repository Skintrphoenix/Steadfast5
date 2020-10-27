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

namespace pocketmine;

use pocketmine\block\Block;
use pocketmine\block\Fire;
use pocketmine\level\Position;
use pocketmine\block\Portal;

//Hardcore else if checker for Nether Portals, was awful to write
class PortalChecker{
    
    public static function tryMakePortal(Block $block) {
        if($block instanceof Fire) {
            if(self::isObsidian($block->getSide(0)->getId())) {
                $x = $block->getX();
                $y = $block->getY();
                $z = $block->getZ();
                if(self::isObsidian($block->getLevel()->getBlockIdAt($x + 1, $y - 1, $z))) { //X axis first, then Z
                    if(self::isObsidian($block->getLevel()->getBlockIdAt($x + 2, $y, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x + 2, $y + 1, $z)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x + 2, $y + 2, $z))) && self::isObsidian($block->getLevel()->getBlockIdAt($x - 1, $y, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x - 1, $y + 1, $z)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x - 1, $y + 2, $z))) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 3, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x + 1, $y + 3, $z))) {
                        for($i = 0; $i < 3; $i++) {
                            $block->getLevel()->setBlock(new Position($x, $y + $i, $z, $block->getLevel()), new Portal(), true);
                            $block->getLevel()->setBlock(new Position($x + 1, $y + $i, $z, $block->getLevel()), new Portal(), true);
                        }
                    }
                } else if(self::isObsidian($block->getLevel()->getBlockIdAt($x - 1, $y - 1, $z))) {
                    if(self::isObsidian($block->getLevel()->getBlockIdAt($x - 2, $y, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x - 2, $y + 1, $z)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x - 2, $y + 2, $z))) && self::isObsidian($block->getLevel()->getBlockIdAt($x + 1, $y, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x + 1, $y + 1, $z)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x + 1, $y + 2, $z))) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 3, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x - 1, $y + 3, $z))) {
                        for($i = 0; $i < 3; $i++) {
                            $block->getLevel()->setBlock(new Position($x, $y + $i, $z, $block->getLevel()), new Portal(), true);
                            $block->getLevel()->setBlock(new Position($x - 1, $y + $i, $z, $block->getLevel()), new Portal(), true);
                        }
                    }
                } else if(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y - 1, $z + 1))) { //X axis first, then Z
                    if(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y, $z + 2)) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 1, $z + 2)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 2, $z + 2))) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y, $z - 1)) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 1, $z - 1)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 2, $z - 1))) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 3, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 3, $z + 1))) {
                        for($i = 0; $i < 3; $i++) {
                            $block->getLevel()->setBlock(new Position($x, $y + $i, $z, $block->getLevel()), new Portal(), true);
                            $block->getLevel()->setBlock(new Position($x, $y + $i, $z + 1, $block->getLevel()), new Portal(), true);
                        }
                    }
                } else if(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y - 1, $z - 1))) {
                    if(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y, $z - 2)) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 1, $z - 2)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 2, $z - 2))) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y, $z + 1)) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 1, $z + 1)) && self::isObsidian(self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 2, $z + 1))) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 3, $z)) && self::isObsidian($block->getLevel()->getBlockIdAt($x, $y + 3, $z - 1))) {
                        for($i = 0; $i < 3; $i++) {
                            $block->getLevel()->setBlock(new Position($x, $y + $i, $z, $block->getLevel()), new Portal(), true);
                            $block->getLevel()->setBlock(new Position($x, $y + $i, $z - 1, $block->getLevel()), new Portal(), true);
                        }
                    }
                } 
            }
        }
    }
    
    private static function isObsidian($id) {
        if($id == Block::OBSIDIAN) {
            return true;
        } else {
            return false; //Can't just return $id === OBSIDIAN due to PHP weirdness
        }
    }
    
}

?>
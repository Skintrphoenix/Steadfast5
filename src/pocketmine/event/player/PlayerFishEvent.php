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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\entity\FishingHook;
use pocketmine\Player;

class PlayerFishEvent extends PlayerEvent implements Cancellable{
    public static $handlerList = null;
    
    private $fisher;
    private $item;
    private $rod;
    
    public function __construct(Player $p, Item $item, FishingHook $rod){
        $this->rod = $rod;
        $this->fisher = $p;
        $this->item = $item;
    }
    
    public function getFisher(){
        return $this->fisher;
    }
    
    public function getItem(){
        return $this->item;
    }
    
    public function setItem(Item $stuff){
        $this->item = $stuff;
    }
    
    public function getHook(){
        return $this->rod;
    }
}
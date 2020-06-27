<?php

namespace pocketmine\tile;

use pocketmine\level\format\FullChunk;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;

class Bed extends Spawnable {
	
	/**
	 * Bed constructor.
	 *
	 * @param FullChunk $level
	 * @param Compound $nbt
	 */
	public function __construct(FullChunk $chunk, Compound $nbt){
		if(!isset($nbt->color) or !($nbt->color instanceof ByteTag)){
			$nbt->color = new ByteTag("color", 14);
		}
		parent::__construct($chunk, $nbt);
	}
	
	/**
	 * @return int
	 */
	public function getColor() : int{
		return $this->namedtag->color->getValue();
	}

	public function getSpawnCompound() {
		return new Compound("", [
			new StringTag("id", Tile::BED),
			new IntTag("x", (int) $this->x),
			new IntTag("y", (int) $this->y),
			new IntTag("z", (int) $this->z),
			$this->namedtag->color,
			new ByteTag("isMovable", (int) $this->namedtag["isMovable"])
		]);
	}

}

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

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\Player;

class ItemFrameDropItemEvent extends BlockEvent implements Cancellable{
	public static $handlerList = null;

	/** @var \pocketmine\Player */
	private $player;
	/** @var \pocketmine\item\Item */
	private $item;
	private $dropChance;

	/**
	 * @param Block    $block
	 * @param Player   $player
	 * @param Item     $dropItem
	 * @param Float    $dropChance
	 */
	public function __construct(Block $block, Player $player, Item $dropItem, $dropChance){
		parent::__construct($block);
		$this->player = $player;
		$this->item = $dropItem;
		$this->dropChance = (float) $dropChance;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}

	/**
	 * @return Item
	 */
	public function getDropItem(){
		return $this->item;
	}

	public function setDropItem(Item $item){
		$this->item = $item;
	}

	/**
	 * @return Float
	 */
	public function getItemDropChance(){
		return $this->dropChance;
	}

	public function setItemDropChance($chance){
		$this->dropChance = (float) $chance;
	}
}
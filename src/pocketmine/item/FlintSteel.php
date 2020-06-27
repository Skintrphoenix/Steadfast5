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

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\Fire;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use function assert;

class FlintSteel extends Tool{

	/** @var Vector3 */
	private $temporalVector = null;

	public function __construct($meta = 0, $count = 1) {
		parent::__construct(self::FLINT_STEEL, $meta, $count, "Flint and Steel");
		if($this->temporalVector === null){
			$this->temporalVector = new Vector3(0, 0, 0);
		}
	}

	public function canBeActivated() : bool{
		return true;
	}

	public function onActivate(Level $level, Player $player, Block $blockReplace, Block $blockClicked, $face,  $fx, $fy, $fz) {

		$tx = $blockClicked->getX();
		$ty = $blockClicked->getY();
		$tz = $blockClicked->getZ();
		$clickVector = new Vector3($tx, $ty, $tz);

		if($blockReplace->getId() === self::AIR and ($blockClicked instanceof Solid)){
			assert($level !== null);
			$level->setBlock($blockReplace, new Fire(), true);
			return true;
		}

		return false;
	}

	public function getMaxDurability() : int{
		return 65;
	}
}

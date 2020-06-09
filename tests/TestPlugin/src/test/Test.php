<?php

namespace test;

use pocketmine\plugin\PluginBase;

class Test extends PluginBase {
	public function onEnable() {
		$this->getServer()->shutdown();
	}
}

<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\level\weather\Weather;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class WeatherCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Set weather for level",
			"/weather <level-name weather|weather (rain|sunny|clear)>"
		);
		$this->setPermission("pocketmine.command.weather");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			$sender->sendMessage("Usage: " . $this->usageMessage);

			return false;
		}

		if($sender instanceof Player){
			$wea = Weather::getWeatherFromString($args[0]);
			if(!isset($args[1])) $duration = mt_rand(min($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax), max($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax));
			else $duration = (int) $args[1];
			if($wea >= 0 and $wea <= 3){
				$sender->getLevel()->getWeather()->setWeather($wea, $duration);
				$sender->sendMessage("Weather changed successfully in level " . $sender->getLevel()->getFolderName() . "!");
				return true;
				/*if(WeatherManager::isRegistered($sender->getLevel())){
					$sender->getLevel()->getWeather()->setWeather($wea, $duration);
					$sender->sendMessage("Weather changed successfully in level " . $sender->getLevel()->getFolderName() . "!");
					return true;
				}else{
					$sender->sendMessage("level " . $sender->getLevel()->getFolderName() . " hasn't registered to WeatherManager.");
					return false;
				}*/
			}else{
				$sender->sendMessage(TextFormat::RED . "Invalid Weather.");
				return false;
			}
		}

		if(count($args) < 2){
			$sender->sendMessage("Usage: " . $this->usageMessage);
			return false;
		}

		$level = $sender->getServer()->getLevelByName($args[0]);
		if(!$level instanceof Level){
			$sender->sendMessage(TextFormat::RED . "Invalid Weather.");
			return false;
		}

		$wea = Weather::getWeatherFromString($args[1]);
		if(!isset($args[1])) $duration = mt_rand(min($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax), max($sender->getServer()->weatherRandomDurationMin, $sender->getServer()->weatherRandomDurationMax));
		else $duration = (int) $args[1];
		if($wea >= 0 and $wea <= 3){
			$level->getWeather()->setWeather($wea, $duration);
			$sender->sendMessage("Weather changed successfully in level " . $args[0] . "!");
			return true;
			/*if(WeatherManager::isRegistered($level)){
				$level->getWeather()->setWeather($wea, $duration);
				$sender->sendMessage("Weather changed successfully in level " . $sender->getLevel()->getFolderName() . "!");
				return true;
			}else{
				$sender->sendMessage("level " . $sender->getLevel()->getFolderName() . " hasn't registered to WeatherManager.");
				return false;
			}*/
		}else{
			$sender->sendMessage(TextFormat::RED . "Invalid Weather.");
			return false;
		}
	}
}

<?php

/*
 * Copyright (C) 2019 alvin0319
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

declare(strict_types=1);
namespace Wild;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use UnexpectedValueException;

use function rmdir as rm;
use function file_exists;
use function scandir;
use function substr;
use function is_file;
use function is_dir;
use function array_diff;

function rmdir(string $dir){
	if(substr($dir, -1) !== "/"){
		$dir .= "/";
	}
	foreach(scandir($dir) as $file){
		if($file !== "." and $file !== ".."){
			$realPath = $dir . $file;

			if(file_exists($realPath)){
				if(is_file($realPath)){
					unlink($realPath);
				}elseif(is_dir($realPath)){
					$ssss = array_diff(scandir($dir . $file), [".", ".."]);

					if(empty($ssss)){
						rm($realPath);
					}else{
						rmdir($realPath);
					}
				}else{
					throw new UnexpectedValueException();
				}
			}
		}
	}

	$ssss = array_diff(scandir($dir), [".", ".."]);

	if(empty($ssss)){
		rm($dir);
	}else{
		rmdir($dir);
	}
}

class Wild extends PluginBase{


	/** @var Config */
	protected $config;

	public function onEnable(){

		$this->config = new Config($this->getDataFolder() . "Config.yml", Config::YAML, [
			"date" => self::convertEnglishToKorean(date("D")),
			"cleaned" => false
		]);

		if(!$this->getServer()->isLevelGenerated("wild") or !$this->getServer()->isLevelLoaded("wild")){
			if(!$this->getServer()->isLevelGenerated("wild")){
				$this->getServer()->generateLevel("wild");
			}else{
				$this->getServer()->loadLevel("wild");
			}
		}

		if(self::convertEnglishToKorean(date("D")) === "일"){
			if ((bool) $this->config->getNested("cleaned") === false){
				$this->config->setNested("cleaned", true);
				$this->getLogger()->notice("야생을 생성한지 7일이 지나 야생을 재 생성 합니다...");
				$this->getServer()->unloadLevel($this->getServer()->getLevelByName("wild"));
				$this->cleanWild();
				$this->getServer()->generateLevel("wild", 404);
				$this->getServer()->loadLevel("wild");
			}
		}

		if(self::convertEnglishToKorean(date("D")) === "월"){
			$this->config->setNested("cleaned", false);
		}
	}

	public static function convertEnglishToKorean(string $date) : string{
		switch($date){
			case "Mon":
				return "월";
			case "Tue":
				return "화";
			case "Wed":
				return "수";
			case "Thu":
				return "목";
			case "Fri":
				return "금";
			case "Sat":
				return "토";
			case "Sun":
				return "일";
			default:
				throw new \UnexpectedValueException("Unexpected Date value $date");
		}
	}

	public function cleanWild(){
		if(file_exists($this->getServer()->getDataPath() . "worlds/wild") and is_dir($this->getServer()->getDataPath() . "worlds/wild")){
			rmdir($this->getServer()->getDataPath() . "worlds/wild");
		}
	}

	public function onDisable(){
		$this->config->save();
	}
}
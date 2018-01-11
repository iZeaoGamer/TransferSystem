<?php

/**
 * TransferSystem plugin for PocketMine-MP, spoons and Steadfast
 * @author Encritary
 */

namespace Encritary;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;

class TrasferSystem extends PluginBase{

	public const DEFAULT_TIMEOUT = 20;

	/** @var string */
	private $serverConfig;
	/** @var string */
	private $ip;

	public function onEnable(){
		$this->getLogger()->info("Loading config...");
		$this->serverConfig = new Config(getenv("HOME") . "/.transfersystem/" . $this->getServer()->getPort() . ".yml", Config::YAML, ["isMain" => $this->getServer()->getPort() === 19132, "allowDirectConnection" => $this->getServer()->getPort() === 19132]);
		$this->ip = Utils::isOnline() ? Utils::getIP() : "127.0.0.1";
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getLogger()->info("TransferSystem was successfully enabled!");
	}

	//API
	public function getIP() : string{
		return $this->ip ?? "127.0.0.1";
	}

	public function getPlayerHash(Player $player) : string{
		//TODO: change this to something else, XUID for example
		return base64_encode($player->getName() . $player->getAddress() . $player->getClientId());
	}

	public function wasTransferedHere(Player $player) : bool{
		$data = new Config(getenv("HOME") . "/.transfersystem/data/" . $this->getPlayerHash($player), ["destinationPort" => -1, "timeout" => -1]);
		if($data->get("destinationPort") !== $this->getServer()->getPort() or $data->get("timeout") === -1){
			return false;
		}
		if($data->get("timeout") > time()){
			$data->setAll(["destinationPort" => -1, "timeout" => -1]);
			$data->save();
			return false;
		}
		return true;
	}

	public function onTransferTo(Player $player, string $address, int $port) : void{
		if(gethostname($address) !== "127.0.0.1" and gethostbyname($address) !== $this->ip){ //TODO: make this work in LAN
			return;
		}
		$data = new Config(getenv("HOME") . "/.transfersystem/data/" . $this->getPlayerHash($player));
		$data->set("destinationPort", $port);
		$data->set("timeout", time() + self::DEFAULT_TIMEOUT);
		$data->save();
	}

}
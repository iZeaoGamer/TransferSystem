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

class TransferSystem extends PluginBase{

	const DEFAULT_TIMEOUT = 60;

	/** @var string */
	private $serverConfig;
	/** @var bool */
	private $isSteadfast = false;

	public function onEnable(){
		$this->getLogger()->info("Loading config...");
		@mkdir(getenv("HOME") . "/.transfersystem/");
		@mkdir(getenv("HOME") . "/.transfersystem/data/");
		$this->serverConfig = new Config(getenv("HOME") . "/.transfersystem/" . $this->getServer()->getPort() . ".yml", Config::YAML, ["allowDirectConnection" => $this->getServer()->getPort() === 19132]);
		if(class_exists("\\pocketmine\\network\\protocol\\TransferPacket") and method_exists("\\pocketmine\\Player", "getClientSecret")){
			$this->isSteadfast = true;
			$this->getServer()->getPluginManager()->registerEvents(new SteadfastEventListener($this), $this);
		}elseif(!class_exists("\\pocketmine\\event\\player\\PlayerTransferEvent")){
			$this->getServer()->getPluginManager()->registerEvents(new OldEventListener($this), $this);
		}else{
			$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		}
		$this->getLogger()->info("TransferSystem was successfully enabled!");
	}

	//API
	public function getIP() : string{
		$ip = Utils::getIP();
		return $ip !== false ? $ip : "127.0.0.1";
	}

	public function getPlayerHash(Player $player) : string{
		if($this->isSteadfast){
			return base64_encode($player->getName() . $player->getAddress() . $player->getClientSecret());
		}else{
			return base64_encode($player->getName() . $player->getAddress() . $player->getClientId());
		}
	}

	public function wasTransferedHere(Player $player) : bool{
		if($this->serverConfig->get("allowDirectConnection")){
			return true;
		}
		$path = getenv("HOME") . "/.transfersystem/data/" . $this->getPlayerHash($player);
		$data = new Config($path, Config::YAML, ["destinationPort" => -1, "timeout" => -1]);
		if($data->get("destinationPort") === -1 or $data->get("timeout") === -1){
			@unlink($path);
			return false;
		}
		if($data->get("destinationPort") !== $this->getServer()->getPort()){
			return false;
		}
		if(time() > $data->get("timeout")){
			@unlink($path);
			return false;
		}
		@unlink($path);
		return true;
	}

	public function onTransferTo(Player $player, string $address, int $port){
		if(gethostbyname($address) !== "127.0.0.1" and gethostbyname($address) !== $this->getIP()){ //TODO: make this work in LAN
			return;
		}
		$data = new Config(getenv("HOME") . "/.transfersystem/data/" . $this->getPlayerHash($player), Config::YAML);
		$data->set("destinationPort", $port);
		$data->set("timeout", time() + self::DEFAULT_TIMEOUT);
		$data->save();
	}

}

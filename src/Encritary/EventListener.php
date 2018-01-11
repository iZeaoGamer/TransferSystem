<?php

/**
 * TransferSystem plugin for PocketMine-MP, spoons and Steadfast
 * @author Encritary
 */

namespace Encritary;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerTransferEvent; //For pmmp and spoons
//For Steadfast:
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\TransferPacket;

class EventListener implements Listener{
	
	/** @var TransferSystem */
	private $ts;

	public function __construct(TransferSystem $ts){
		$this->ts = $ts;
	}

	public function onPlayerPreLogin(PlayerPreLoginEvent $event){
		if(!$this->ts->wasTransferedHere($event->getPlayer())){
			$event->getPlayer()->transfer($this->ts->getIP(), 19132);
			$event->setCancelled();
		}
	}

	public function onPlayerTransfer(PlayerTransferEvent $event){
		$this->ts->onTransferTo($event->getPlayer(), $event->getAddress(), $event->getPort());
	}

	public function onDataPacketSend(DataPacketSendEvent $event){
		if(!class_exists("\\pocketmine\\network\\protocol\\TransferPacket")){
			return;
		}
		if($event->getPacket() instanceof TransferPacket){
			$this->ts->onTransferTo($event->getPlayer(), $event->getPacket()->ip, $event->getPacket()->port);
		}
	}

}
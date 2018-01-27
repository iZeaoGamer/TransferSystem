<?php

/**
 * TransferSystem plugin for PocketMine-MP, spoons and Steadfast
 * @author Encritary
 */

namespace Encritary;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\protocol\TransferPacket;

class SteadfastEventListener implements Listener{

	/** @var TransferSystem */
	private $ts;

	public function __construct(TransferSystem $ts){
		$this->ts = $ts;
	}

	public function onPlayerLogin(PlayerLoginEvent $event){
		if(!$this->ts->wasTransferedHere($event->getPlayer())){
			$event->getPlayer()->transfer($this->ts->getIP(), 19132);
		}
	}

	/**
	 * @ignoreCancelled true
	 */
	public function onDataPacketSend(DataPacketSendEvent $event){
		if($event->getPacket() instanceof TransferPacket){
			$this->ts->onTransferTo($event->getPlayer(), $event->getPacket()->ip, $event->getPacket()->port);
		}
	}

}

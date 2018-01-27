<?php

/**
 * TransferSystem plugin for PocketMine-MP, spoons and Steadfast
 * @author Encritary
 */

namespace Encritary;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerTransferEvent;

class EventListener implements Listener{

	/** @var TransferSystem */
	private $ts;

	public function __construct(TransferSystem $ts){
		$this->ts = $ts;
	}

	public function onPlayerLogin(PlayerLoginEvent $event){
		if(!$this->ts->wasTransferedHere($event->getPlayer())){
			$event->getPlayer()->transfer($this->ts->getIP(), 19132);
			$event->setCancelled();
		}
	}

	/**
	 * @priority HIGHEST
	 * @ignoreCancelled true
	 */
	public function onPlayerTransfer(PlayerTransferEvent $event){
		$this->ts->onTransferTo($event->getPlayer(), $event->getAddress(), $event->getPort());
	}

}

<?php namespace pocketmine\event\player;
use pocketmine\event\Cancellable;
use pocketmine\Player;
class PlayerPreLoginEvent extends PlayerEvent implements Cancellable {
	public static $handlerList = null;
	protected $kickMessage;
	public function __construct(Player $player, $kickMessage){
		$this->player = $player;
		$this->kickMessage = $kickMessage;
	}
	public function setKickMessage($kickMessage){
		$this->kickMessage = $kickMessage;
	}
	public function getKickMessage(){
		return $this->kickMessage;
	}
}
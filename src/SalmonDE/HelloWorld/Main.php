<?php
namespace SalmonDE\HelloWorld;

use pocketmine\Player;

class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener
{

    private $message;
    private $global;

    private $players = [];

    public function onEnable(){
        $this->saveResource('config.yml');

        $this->message = (string) $this->getConfig()->get('message');
        $this->global = (bool) $this->getConfig()->get('global');

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(\pocketmine\command\CommandSender $sender, \pocketmine\command\Command $cmd, $label, array $args){
        if(!isset($args[0])){
            return false;
        }

        if(!(($target = $this->getServer()->getPlayer($args[0])) instanceof Player)){
            $sender->sendMessage('Player not found!');
        }

        if($this->isPlayerInArray($target) !== false){
            $this->removePlayer($target);
        }else{
            $this->addPlayer($target);
        }
    }

    public function getMessage() : string{
        return $this->message;
    }

    public function setMessage(string $message){
        $this->message = $message;
    }

    public function isGlobal() : bool{
        return $this->global;
    }

    public function setGlobal($bool = true){
        $this->global = $bool;
    }

    public function getPlayers() : array{
        return $this->players;
    }

    public function isPlayerInArray(Player $player){
        return array_search($player, $this->players);
    }

    public function addPlayer(Player $player){
        if($this->isPlayerInArray($player) === false){
            $this->players[] = $player;
            return true;
        }

        return false;
    }

    public function removePlayer(Player $player){
        if(($index = $this->isPlayerInArray($player)) !== false){
            unset($this->players[$index]);
            return true;
        }

        return false;
    }

    public function onPacketSend(\pocketmine\event\server\DataPacketSendEvent $event){
        if($event->getPacket()::NETWORK_ID === \pocketmine\network\mcpe\protocol\ProtocolInfo::TEXT_PACKET){
            if($this->isGlobal() || $this->isPlayerInArray($event->getPlayer()) !== false){
                $event->getPacket()->message = $this->message;
            }
        }
    }

    public function onQuit(\pocketmine\event\player\PlayerQuitEvent $event){
        $this->removePlayer($event->getPlayer());
    }
}

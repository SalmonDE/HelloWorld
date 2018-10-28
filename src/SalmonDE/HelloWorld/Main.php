<?php
declare(strict_types = 1);

namespace SalmonDE\HelloWorld;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener {

    private $message;
    private $global;

    private $players = [];

    public function onEnable(): void{
        $this->saveResource('config.yml');

        $this->message = (string) $this->getConfig()->get('message');
        $this->global = (bool) $this->getConfig()->get('global');

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $cmd, $label, array $params): bool{
        if(!isset($params[0])){
            return false;
        }

        if(!(($target = $this->getServer()->getPlayer($params[0])) instanceof Player)){
            $sender->sendMessage('Player not found!');
        }

        if($this->isPlayerInArray($target) !== false){
            $this->removePlayer($target);
        }else{
            $this->addPlayer($target);
        }

        return true;
    }

    public function getMessage(): string{
        return $this->message;
    }

    public function setMessage(string $message): void{
        $this->message = $message;
    }

    public function isGlobal(): bool{
        return $this->global;
    }

    public function setGlobal($bool = true): void{
        $this->global = $bool;
    }

    public function getPlayers(): array{
        return $this->players;
    }

    public function getIndexOfPlayer(Player $player){
        return array_search($player, $this->players);
    }

    public function isPlayerInArray(Player $player): bool{
        return $this->getIndexOfPlayer($player) !== false;
    }

    public function addPlayer(Player $player): bool{
        if(!$this->isPlayerInArray($player)){
            $this->players[] = $player;
            return true;
        }

        return false;
    }

    public function removePlayer(Player $player): bool{
        if(($index = $this->getIndexOfPlayer($player)) !== false){
            unset($this->players[$index]);
            return true;
        }

        return false;
    }

    public function onPacketSend(DataPacketSendEvent $event): void{
        if($event->getPacket()::NETWORK_ID === ProtocolInfo::TEXT_PACKET){
            if($this->isGlobal() || $this->isPlayerInArray($event->getPlayer())){
                $event->getPacket()->message = $this->message;
            }
        }
    }

    public function onQuit(PlayerQuitEvent $event): void{
        $this->removePlayer($event->getPlayer());
    }
}

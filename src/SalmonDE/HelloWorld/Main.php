<?php
namespace SalmonDE\HelloWorld;

class Main extends \pocketmine\plugin\PluginBase implements \pocketmine\event\Listener
{

    private $message;

    public function onEnable(){
        $this->saveResource('config.yml');

        $this->message = $this->getConfig()->get('message');

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPacketSend(\pocketmine\event\server\DataPacketSendEvent $event){
        if($event->getPacket()::NETWORK_ID === \pocketmine\network\mcpe\protocol\ProtocolInfo::TEXT_PACKET){
            $event->getPacket()->message = $this->message;
        }
    }
}

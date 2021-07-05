<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote;

use AkmalFairuz\PMRemote\client\Client;
use AkmalFairuz\PMRemote\handler\HttpHandler;
use pocketmine\plugin\PluginBase;
use pocketmine\snooze\SleeperNotifier;
use Throwable;

class Main extends PluginBase{

    const VERSION = "1.0.0";

    const SERVER = "PMRemote/".self::VERSION;

    /** @var HttpThread */
    private $httpThread;

    /** @var HttpHandler */
    private $handler;

    public function onEnable(){
        $this->saveResources();
        $config = $this->getConfig();

        $this->handler = new HttpHandler();

        $sleeper = $this->getServer()->getTickSleeper();
        $notifier = new SleeperNotifier();
        $this->httpThread = new HttpThread($config->get("http-bind-ip"), $config->get("http-bind-port"), $this->getDataFolder()."public_html", $notifier, $this->getServer()->getLogger(), $this->getServer()->getLoader());
        $sleeper->addNotifier($notifier, function() {
            try{
                $this->httpThread->response = $this->handler->handle(new Client($this->httpThread->requestIp, $this->httpThread->requestPort, $this->httpThread->requestBuffer));
            } catch(Throwable $t) {
                $this->httpThread->response = $this->handler->handleError($t);
            }

            $this->httpThread->synchronized(function(HttpThread $thread) {
                $thread->notify();
            }, $this->httpThread);
        });
        $this->httpThread->start(PTHREADS_INHERIT_NONE);
    }

    private function saveResources() {
        $this->saveResource("config.yml");
        $this->saveResource("public_html/favicon.ico");
    }

    public function onDisable(){
        $this->httpThread->shutdown();
    }
}
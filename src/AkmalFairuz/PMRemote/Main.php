<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote;

use AkmalFairuz\PMRemote\client\Client;
use AkmalFairuz\PMRemote\controller\HomeController;
use AkmalFairuz\PMRemote\handler\HttpHandler;
use AkmalFairuz\PMRemote\utils\Route;
use pocketmine\plugin\PluginBase;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\utils\Config;
use Throwable;

class Main extends PluginBase{

    const VERSION = "1.0.0";

    const SERVER = "PMRemote/".self::VERSION;

    /** @var HttpThread */
    private $httpThread;

    /** @var HttpHandler */
    private $handler;

    /** @var self */
    private static $instance;

    /** @var Config */
    private $config;

    public static function getInstance() {
        return self::$instance;
    }

    public function onEnable(){
        self::$instance = $this;

        $this->saveResources();
        $this->config = $this->getConfig();

        $this->registerRoutes();
        $this->handler = new HttpHandler();

        $sleeper = $this->getServer()->getTickSleeper();
        $notifier = new SleeperNotifier();
        $this->httpThread = new HttpThread($this->config->get("http-bind-ip"), $this->config->get("http-bind-port"), $this->getDataFolder()."public_html", $notifier, $this->getServer()->getLogger(), $this->getServer()->getLoader());
        $sleeper->addNotifier($notifier, function() {
            try{
                $this->httpThread->response = $this->handler->handle(new Client($this->httpThread->requestIp, $this->httpThread->requestPort, $this->httpThread->requestBuffer));
            } catch(Throwable $t) {
                if($this->isDebug()){
                    $this->httpThread->response = $this->handler->handleError($t);
                }
            }

            $this->httpThread->synchronized(function(HttpThread $thread) {
                $thread->notify();
            }, $this->httpThread);
        });
        $this->httpThread->start(PTHREADS_INHERIT_NONE);
    }

    private function registerRoutes() {
        Route::get("/", HomeController::class, "index");
    }

    public function isDebug() {
        return $this->config->get("debug-mode", true);
    }

    private function saveResources() {
        foreach($this->getResources() as $key => $file) {
            $this->saveResource($key);
        }
    }

    public function onDisable(){
        $this->httpThread->shutdown();
    }
}
<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\Thread;
use ClassLoader;
use AttachableThreadedLogger;

class HttpThread extends Thread{

    /** @var bool */
    public $shutdown;

    /** @var AttachableThreadedLogger */
    private $logger;

    /** @var resource */
    private $socket;

    /** @var string */
    public $response = "";

    /** @var string */
    public $requestBuffer = "";

    /** @var string */
    public $requestIp = "";

    /** @var int */
    public $requestPort = "";

    /** @var SleeperNotifier */
    public $notifier;

    /** @var string */
    private $rootPath;

    public function __construct(string $ip, int $port, string $rootPath, SleeperNotifier $notifier, AttachableThreadedLogger $logger, ClassLoader $loader){
        $this->setClassLoader($loader);
        $this->logger = $logger;
        $this->notifier = $notifier;
        $this->rootPath = $rootPath;

        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_set_nonblock($this->socket);
        socket_set_option($this->socket, SOL_TCP, TCP_NODELAY, 1);
        socket_bind($this->socket, $ip, $port);
        socket_listen($this->socket, 3);
    }

    public function run(){
        $this->registerClassLoader();
        gc_enable();
        error_reporting(-1);
        ini_set("display_errors", "1");
        ini_set("display_startup_errors", "1");

        register_shutdown_function([$this, "shutdown"]);

        $this->tickProcessor();
    }

    public function shutdown() {
        $this->shutdown = true;
    }

    private function tickProcessor() {
        /** @var string[] $blockedIps */
        $blockedIps = [];

        while(!$this->shutdown) {
            $start = microtime(true);
            $this->onTick($blockedIps);
            $time = microtime(true);
            if ($time - $start < 0.01) {
                time_sleep_until($time + 0.01 - ($time - $start));
            }
        }

        $this->close();
    }

    private function onTick(&$blockedIps) {
        if(($client = socket_accept($this->socket)) !== false){
            $buf = @socket_read($client, 65535);
            if($buf != null){
                $path = explode(" ", $buf)[1] ?? null;
                socket_getpeername($client, $ip, $port);
                if(isset($blockedIps[$ip])) {
                    if($blockedIps[$ip] >= time()) {
                        unset($blockedIps[$ip]);
                        @socket_write($client, "HTTP/1.1 200 OK\r\nLocation: /");
                    }
                }else{
                    if($path === null){
                        // invalid http request (possible hack attempt), block for 10 minutes
                        $blockedIps[$ip] = time() + 600;
                        return;
                    }else{
                        $fullPath = $this->rootPath . $path;
                        if(is_file($fullPath)){
                            @socket_write($client, "HTTP/1.1 200 OK\r\nContent-Type: " . $this->getContentType($fullPath) . "\r\nServer: " . Main::SERVER . "\r\n\r\n" . file_get_contents($fullPath));
                        }else{
                            $this->requestBuffer = $buf;

                            $this->requestIp = $ip;
                            $this->requestPort = $port;

                            $this->synchronized(function() : void{
                                $this->notifier->wakeupSleeper();
                                $this->wait();
                            });
                            @socket_write($client, $this->response);

                            [$this->response, $this->requestIp, $this->requestPort] = "";
                        }
                    }
                }
                $this->disconnect($client);
            }
        }
    }

    private function getContentType(string $path) {
        $e = explode(".", $path);
        $e = $e[count($e) - 1];
        switch($e) {
            case "ico":
                return "image/x-icon";
            case "css":
                return "text/css";
            case "js":
                return "text/javascript";
            default:
                return "text/plain";
        }
    }

    private function close() {
        @socket_shutdown($this->socket);
        unset($this->socket);
    }

    private function disconnect($socket) {
        @socket_close($socket);
    }
}
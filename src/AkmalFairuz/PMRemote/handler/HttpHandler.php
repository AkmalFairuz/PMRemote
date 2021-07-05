<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote\handler;

use AkmalFairuz\PMRemote\client\Client;
use AkmalFairuz\PMRemote\Main;
use pocketmine\Server;
use Throwable;

class HttpHandler{

    /** @var int */
    private $requestId = 0;

    public function handle(Client $client) {
        // TODO: styling, javascript, more features...
        return $this->buildResponse(
            "<h1>Server TPS: " . Server::getInstance()->getTicksPerSecond() .
            "<br>Players: " . count(Server::getInstance()->getOnlinePlayers()) . "/" . Server::getInstance()->getMaxPlayers() .
            "<br>Server Load: ".Server::getInstance()->getTickUsage()."</h1>");
    }

    public function handleError(Throwable $t) {
        return $this->buildResponse("<h1>500 Internal Server Error</h1>".str_replace("\n", "<br>", $t), "text/html", null, 500, "Internal Server Error");
    }

    private function buildResponse(string $content, string $content_type = "text/html", array $extra_header = null, int $response_code = 200, string $response_name = "OK") {
        $response = "HTTP/1.1 ".$response_code." ".$response_name;
        $headers = [
            "Date: ".gmdate('D, d M Y H:i:s T'),
            "Server: ".Main::SERVER,
            "Content-Type: ".$content_type,
            "Content-Length: ".strlen($content),
            "X-PMRemote-RequestId ".$this->requestId++
        ];
        if($extra_header !== null) {
            array_push($headers, ...$extra_header);
        }
        return $response . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $content;
    }
}
<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote\client;

class Client{

    /** @var array */
    public $post;

    /** @var array */
    public $get;

    /** @var array */
    public $cookie;

    /** @var string */
    public $user_agent;

    /** @var string */
    public $path;

    /** @var string */
    public $ip;

    /** @var int */
    public $port;

    /** @var string */
    public $method;

    /** @var string */
    public $content_type;

    public function __construct(string $ip, int $port, string $buffer){
        $this->ip = $ip;
        $this->port = $port;
        $this->decode($buffer);
    }

    private function decode($buffer) {
        $requestData = explode("\r\n", $buffer);

        $request = explode(" ", $requestData[0]);
        array_shift($requestData);
        $this->method = $request[0];
        $this->path = $request[1];

        foreach($requestData as $headers) {
            array_shift($requestData);
            if($headers === "") {
                break;
            }
            $header = explode(": ",$headers);
            $name = $header[0];
            array_shift($header);
            $value = trim(implode(": ", $header));

            if($name === "User-Agent") {
                $this->user_agent = $value;
            } elseif ($name === "Cookie") {
                $cookies = [];
                foreach(explode(";", $value) as $cookie) {
                    $cookie = explode("=", $cookie);
                    $cookies[urldecode($cookie[0])] = urldecode($cookie[1]);
                }
                $this->cookie = $cookies;
            } elseif ($name === "Content-Type") {
                $this->content_type = $value;
            }
        }
        if($this->method === "POST") {
            $requestData = array_values($requestData);
            if(isset($requestData[0])) {
                if($this->content_type === "application/x-www-form-urlencoded") {
                    $this->post = $this->decodePostUrlEncoded($requestData[0]);
                }
            }
        }
    }

    private function decodePostUrlEncoded(string $encoded) {
        $lists = explode("&", $encoded);
        $arr = [];
        foreach($lists as $list) {
            $post = explode("=", $list);
            $arr[$post[0]] = $post[1];
        }
        return $arr;
    }
}
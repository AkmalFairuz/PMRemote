<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote\utils;

use AkmalFairuz\PMRemote\client\Client;
use AkmalFairuz\PMRemote\controller\HomeController;

class Route{

    /** @var string[] */
    private static $routeGet = [];

    /** @var string[] */
    private static $routePost = [];

    public static function run(Client $client) {
        $page = self::route($client);
        if($page === null) {
            $page = (new HomeController())->notFound($client);
        }
        return $page;
    }

    public static function get(string $route, string $controller, string $func) {
        self::$routeGet[$route] = $controller."@".$func;
    }

    public static function post(string $route, string $controller, string $func) {
        self::$routePost[$route] = $controller."@".$func;
    }

    private static function route(Client $client) {
        $uri = $client->path;
        $uri = explode("?", $uri)[0];
        if(strlen($uri) > 1) {
            if($uri[strlen($uri) - 1] === "/") {
                $uri = substr($uri, 0, strlen($uri) - 1);
            }
        }
        $path = explode("/", $uri);
        $routes = $client->method === "POST" ? self::$routePost : self::$routeGet;
        foreach($routes as $route => $controller) {
            $controller = explode("@", $controller);
            $func = $controller[1];
            $controller = $controller[0];

            $hasParams = strpos($route, "{") !== false;
            if(!$hasParams && $uri === $route) {
                return (new $controller())->{$func}($client);
            }
            $rP = explode("/", $route);
            $params = [];
            foreach($path as $cnt => $s) {
                if(isset($rP[$cnt])) {
                    if(strpos($route, "{") === false) {
                        if($rP[$cnt] !== $s) {
                            continue 2;
                        }
                    }
                } else {
                    continue 2;
                }
            }
            foreach($rP as $cnt => $ps) {
                if(isset($ps[0]) && $ps[0] === "{" && $ps[strlen($ps) - 1] === "}") {
                    $params[str_replace(["{", "}"], "", $ps)] = $path[$cnt];
                }elseif(!(isset($path[$cnt]) && $ps === $path[$cnt])) {
                    continue 2;
                }
            }
            foreach($params as $k => $v) {
                $params[$k] = urldecode($v);
            }
            return (new $controller())->{$func}($client, ...$params);
        }
        return null;
    }
}
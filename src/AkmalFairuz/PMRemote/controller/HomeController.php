<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote\controller;

use AkmalFairuz\PMRemote\client\Client;
use AkmalFairuz\PMRemote\utils\Style;
use pocketmine\Server;
use pocketmine\utils\Utils;

class HomeController extends Controller{

    public function index(Client $client) {
        return Style::create('Home')->section('container', '<br><h1 class="text-center">Server Overview</h1><br>

<div class="row text-center">
<div class="col-md-3">
<div class="card">
<div class="card-header bg-primary h2">
Server Load
</div>
<div class="card-body h4">
' . floor(Server::getInstance()->getTickUsage()) . '%
</div>
</div>
</div>
<br>
<div class="col-md-3">
<div class="card">
<div class="card-header bg-primary h2">
TPS
</div>
<div class="card-body h4">
' . floor(Server::getInstance()->getTicksPerSecond()) . '
</div>
</div>
</div>
<br>
<div class="col-md-3">
<div class="card">
<div class="card-header bg-primary h2">
Players
</div>
<div class="card-body h4">
' . count(Server::getInstance()->getOnlinePlayers()) . ' / ' . Server::getInstance()->getMaxPlayers() . '
</div>
</div>
</div>
<br>
<div class="col-md-3">
<div class="card">
<div class="card-header bg-primary h2">
RAM Usage
</div>
<div class="card-body h4">
' . number_format(round((Utils::getMemoryUsage(true)[1] / 1024) / 1024, 2), 2) . ' MB
</div>
</div>
</div>
</div>')->render();
    }

    public function notFound(Client $client) {
        return Style::create('Page not found')->section('container', '<br><h1 class="text-center">Page not found</h1>')->render();
    }
}
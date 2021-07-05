<?php

declare(strict_types=1);

namespace AkmalFairuz\PMRemote\style;

class Style{

    public static function create(string $title) {
        $style = new self;
        $style->title = $title;
    }

    /** @var string */
    public $title;

    /** @var string[] */
    public $yield = [];

    private function yield(string $name) : string {
        return $this->yield[$name] ?? "";
    }

    public function section(string $name, $value) {
        if(!isset($this->yield[$name])) {
            $this->yield[$name] = $value;
        } else{
            $this->yield[$name] .= $value;
        }
    }

    public function render() {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <title>' . $this->title . '</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/font-awesome.min.css" rel="stylesheet">
    <link href="/css/pecomm.css" rel="stylesheet">
</head>
<body>
    <header>
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <a class="navbar-brand" href="#">PMRemote</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
              <a class="nav-link" href="/">Home</a>
            </li>
          </ul>
          <span class="mt-2 mt-md-0">
            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Admin</button>
          </span>
        </div>
      </nav>
    </header>
    <main role="main" class="container">
        ' . $this->yield("container") . '
    </main>
    <footer class="footer bg-secondary">
      <div class="container">
        <span class="text-light text-center">
            This server use PMRemote: <a href="https://github.com/AkmalFairuz/PMRemote">GitHub</a>
        </span>
      </div>
    </footer>
</body></html>';
    }
}
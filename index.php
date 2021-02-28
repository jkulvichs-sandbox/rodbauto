<?php

require_once "Main.php";

// App init
use Main\App;

try {
    App::Init("config.json");
} catch (Exception $e) {
    print("can't init app: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}\n");
    print($e->getTraceAsString());
    App::Finalize($e->getCode());
}

// App run
try {
    App::Main();
} catch (Exception $e) {
    print("app error: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}\n");
    print($e->getTraceAsString());
    App::Finalize($e->getCode());
}

// App finalize
App::Finalize();

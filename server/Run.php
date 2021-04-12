<?php

require_once "App.php";

// App init
use Main\App;

function RunAction($action)
{

    App::Init($_SERVER['DOCUMENT_ROOT'] . "/server/config.json");
//    App::Init($_SERVER['DOCUMENT_ROOT'] . "/rodb/server/config.json");
    App::Main($action, $_SERVER['REQUEST_METHOD'], $_REQUEST, file_get_contents('php://input'));
    App::Finalize();

}

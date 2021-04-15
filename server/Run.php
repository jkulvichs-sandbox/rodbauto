<?php

require_once "App.php";

// App init
use Main\App;

function RunAction($action)
{

    App::Init("../../config.json");
    App::Main($action, $_SERVER['REQUEST_METHOD'], $_REQUEST, file_get_contents('php://input'));
    App::Finalize();

}

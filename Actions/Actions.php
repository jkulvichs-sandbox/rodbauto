<?php

namespace Actions {

    require_once "Action.php";
    require_once "Context.php";
    require_once "Response.php";

    /* DESCRIBE ERRORS */

    define("ERROR_INCORRECT_ACTION", "incorrect_action");
    define("ERROR_INIT", "init_error");
    define("ERROR_APP", "app_error");

    /* CONNECT ACTIONS CONSTANTS */

    require_once "ActionError.php";
    define("ACTION_ERROR", "error");

    require_once "ActionTest.php";
    define("ACTION_TEST", "test");

}

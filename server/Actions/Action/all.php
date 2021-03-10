<?php

namespace Action {

    require_once "Action.php";

    require_once "ActionError.php";
    define("ACTION_ERROR", "error");

    require_once "ActionPersonsSearch.php";
    define("ACTION_PERSONS_SEARCH", "persons_search");

    require_once "ActionRecruitOfficesList.php";
    define("ACTION_RECRUIT_OFFICES_LIST", "recruit_offices_list");

}

<?php

namespace Main {

    require_once "Config.php";
    require_once "Postgres/Postgres.php";
    require_once "SQLite/SQLite.php";
    require_once "Actions/all.php";
    require_once "Structures/all.php";

    use Action\ActionError;
    use Action\ActionPersonsSearch;
    use Action\ActionRecruitOfficesList;
    use Action\ActionUpdateExtra;
    use Actions\Context;
    use Actions\Response;
    use AppConfig;
    use ErrorException;
    use Exception;
    use Postgres\Postgres;
    use SQLite3;
    use SQLite\SQLite;

    class App
    {
        /**
         * @var AppConfig\Config App configuration
         */
        private static $config = null;

        /**
         * @var resource Postgres connection
         */
        private static $db = null;

        /**
         * App init and configuration
         * @param string $configName Path to app config file
         */
        public static function Init($configName)
        {
            try {

                // Read and parse app config
                $configJSON = file_get_contents($configName);
                if ($configJSON === false) throw new ErrorException(
                    "can't find the config file: $configName", 1
                );
                self::$config = new AppConfig\Config($configJSON);

                // Apply config's debug settings
                error_reporting(self::$config->debug->getErrorType());
                ini_set('display_errors', self::$config->debug->getErrorsDisplay());

                // Connect to PostgreSQL & configuration
                self::$db = pg_connect(self::$config->postgres->getConnectionString());
                if (self::$db === false) throw new ErrorException(
                    "can't connect to postgres", 1
                );
                pg_set_client_encoding(self::$db, "UNICODE");

            } catch (Exception $e) {
                (new ActionError())->ExecuteError(
                    500,
                    ERROR_INIT,
                    "can't init app: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}"
                );
                App::Finalize($e->getCode());
            }
        }

        /**
         * App main logic and routes
         * @param string $action Action const from Actions file
         * @param string $method Request method
         * @param array $args Associative array with request args|params
         * @param string $body Request body
         */
        public static function Main($action, $method, $args, $body)
        {
            // Request context
            $ctx = null;
            try {

                // Create a Postgres ORM
                $pg = new Postgres(self::$db);

                // Create a SQLite local storage
                $sqlite = new SQLite(new SQLite3(App::$config->sqlite->filename), $pg);

                // Create the request context
                $ctx = new Context(App::$config, $pg, $sqlite, $action, $method, $args, $body);

                // select action and redirect control
                switch ($action) {
                    case ACTION_PERSONS_SEARCH:
                        (new ActionPersonsSearch())->Execute($ctx);
                        break;
                    case ACTION_RECRUIT_OFFICES_LIST:
                        (new ActionRecruitOfficesList())->Execute($ctx);
                        break;
                    case ACTION_UPDATE_EXTRA:
                        (new ActionUpdateExtra())->Execute($ctx);
                        break;
                    default:
                        (new Response())
                            ->AddError(
                                400,
                                ERROR_INCORRECT_ACTION,
                                "incorrect api usage detected, use /api/*.php address or check your route script"
                            )
                            ->AddContext($ctx)
                            ->Reply();
                }

            } catch (Exception $e) {
                (new Response())
                    ->AddError(
                        500,
                        ERROR_APP,
                        "app error: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}"
                    )
                    ->AddContext($ctx)
                    ->Reply();
                App::Finalize($e->getCode());
            }
        }

        /**
         * App finalization
         * @param int $exitCode Finalization code. 0 - is ok
         */
        public static function Finalize($exitCode = 0)
        {
            pg_close(self::$db);
            exit($exitCode);
        }
    }
}

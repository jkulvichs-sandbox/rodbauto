<?php

namespace Main {

    require_once "Config.php";
    require_once "Postgres/Postgres.php";
    require_once "Actions/Actions.php";

    use Actions\ActionError;
    use Actions\Context;
    use Actions\ActionTest;
    use AppConfig;
    use ErrorException;
    use Exception;
    use Postgres\Postgres;

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
            //TODO: В поиске указывается номер команды и выводятся все люди из неё
            // Какой столбец хранит номер локальной команды?
//            $model = (new PersonPriz01($pg))->get("849013300000025");
//            var_dump($model);
            try {

                // Create an Postgres ORM
                $pg = new Postgres(self::$db);

                // Create the request context
                $ctx = new Context(App::$config, $pg, $action, $method, $args, $body);

                // select action and redirect control
                switch ($action) {
                    case ACTION_TEST:
                        (new ActionTest())->Execute($ctx);
                        break;
                    default:
                        (new ActionError())->ExecuteError(
                            ERROR_INCORRECT_ACTION,
                            "incorrect api usage detected, use /api/*.php address or check your route script",
                            $ctx
                        );
                }

            } catch (Exception $e) {
                (new ActionError())->ExecuteError(
                    ERROR_APP,
                    "app error: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}"
                );
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

<?php

namespace Main {

    require_once "AppConfig.php";
    require_once "Postgres/Postgres.php";

    use AppConfig;
    use ErrorException;
    use Models\PersonPriz01;
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
         * @throws ErrorException
         */
        public static function Init($configName)
        {
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
        }

        /**
         * App main logic
         * @throws ErrorException
         */
        public static function Main()
        {
            $pg = new Postgres(self::$db);
            $model = (new PersonPriz01($pg))->get("849013300000025");
            var_dump($model);

            //TODO: В поиске указывается номер команды и выводятся все люди из неё
            // Какой столбец хранит номер локальной команды?
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

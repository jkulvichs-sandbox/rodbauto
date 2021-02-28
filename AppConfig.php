<?php

/*
//TODO: Rewrite with stdClass PHPDoc casting (just classes with fields but with PHPDoc notation)
AppConfig JSON map:

{
    "debug": {
        "errorsDisplay": true,
        "errorsTypes": ["all", "parse", "error", "warning", "notice", "strict", "deprecated"]
    },
    "postgres": {
        "host": "localhost",
        "dbname": "postgre",
        "user": "admin",
        "password": "admin"
    }
}
*/

namespace AppConfig {

    use ErrorException;
    use stdClass;

    /**
     * Class Config with app configuration
     * @package AppConfig
     */
    class Config
    {
        /**
         * @var DebugConfig with debug configuration
         */
        public $debug = null;

        /**
         * @var PostgresConfig with postgres connection configuration
         */
        public $postgres = null;

        /**
         * Config constructor.
         * @param string $json JSON with app config
         */
        public function __construct($json)
        {
            // Map of app config
            $configMap = json_decode($json);

            // DebugConfig
            $this->debug = new DebugConfig($configMap->debug);

            // PostgresConfig
            $this->postgres = new PostgresConfig($configMap->postgres);
        }
    }

    /**
     * Class DebugConfig to configure app debugging and error reporting level
     * @package AppConfig
     */
    class DebugConfig
    {
        /**
         * @var array Text representation of error levels array
         */
        private $errorsTypesStr;

        /**
         * @var bool Display errors in the output or not
         */
        private $errorsDisplay;

        /**
         * DebugConfig constructor.
         * @param stdClass $configDebugMap Debug config data
         */
        public function __construct($configDebugMap)
        {
            $this->errorsTypesStr = $configDebugMap->errorsTypes;
            $this->errorsDisplay = $configDebugMap->errorsDisplay;
        }

        /**
         * Get error type as a sum of all errors types in the $errorTypesStr list
         * @return int Error level
         * @throws ErrorException
         */
        public function getErrorType()
        {
            $errorTypesMap = [
                "all" => E_ALL,
                "parse" => E_PARSE,
                "error" => E_ERROR,
                "warning" => E_WARNING,
                "notice" => E_NOTICE,
                "strict" => E_STRICT,
                "deprecated" => E_DEPRECATED,
            ];
            // Total error level
            $errorCode = 0;
            // Errors summation
            foreach ($this->errorsTypesStr as $errorStr) {
                $code = $errorTypesMap[$errorStr];
                if ($code !== null) $errorCode |= $code;
                else throw new ErrorException("incorrect error type", 1);
            }
            return $errorCode;
        }

        /**
         * Display errors in the output or not
         * @return bool
         */
        public function getErrorsDisplay()
        {
            return $this->errorsDisplay;
        }
    }

    /**
     * Class PostgresConfig to configure PostgreSQL connection
     * @package AppConfig
     */
    class PostgresConfig
    {
        private $host;
        private $dbname;
        private $user;
        private $password;

        /**
         * PostgresConfig constructor/
         * @param stdClass $configPostgresMap Postgres configuration map
         */
        public function __construct($configPostgresMap)
        {
            $this->host = $configPostgresMap->host;
            $this->dbname = $configPostgresMap->dbname;
            $this->user = $configPostgresMap->user;
            $this->password = $configPostgresMap->password;
        }

        /**
         * Make connection string to Postgres
         * @return string
         */
        public function getConnectionString()
        {
            return sprintf(
                "host=%s dbname=%s user=%s password=%s",
                $this->host,
                $this->dbname,
                $this->user,
                $this->password
            );
        }
    }

}

<?php

namespace Actions {

    use AppConfig\Config;
    use Postgres\Postgres;
    use SQLite\SQLite;

    /**
     * Class Context with request context
     * @package Actions
     */
    class Context
    {
        /**
         * Context constructor.
         * @param Config $config App config
         * @param Postgres $pg Postgres ORM and connection
         * @param SQLite $sqlite SQLite local storage instance
         * @param string $action Action constant from Actions file
         * @param string $method HTTP request method
         * @param array $args Associative array of query args|params
         * @param string $body String with request body
         */
        public function __construct($config, $pg, $sqlite, $action, $method, $args, $body)
        {
            $this->config = $config;
            $this->pg = $pg;
            $this->sqlite = $sqlite;
            $this->action = $action;
            $this->method = $method;
            $this->args = $args;
            $this->body = $body;
        }

        /**
         * Add message to log history
         * @param string $message
         */
        public function log($message)
        {
            $this->logHistory[] = $message;
        }

        /**
         * App config
         * @var Config
         */
        public $config;

        /**
         * Postgres ORM and connection
         * @var Postgres
         */
        public $pg;

        /**
         * SQLIte local storage instance
         * @var SQLite
         */
        public $sqlite;

        /**
         * Action constant from Actions file
         * @var string
         */
        public $action;

        /**
         * HTTP request method
         * @var string
         */
        public $method;

        /**
         * Associative array of query args|params
         * @var array
         */
        public $args;

        /**
         * String with request body
         * @var string
         */
        public $body;

        /**
         * Actions log
         * @var array
         */
        public $logHistory = [];
    }

}

<?php

namespace Actions {

    use AppConfig\Config;
    use Postgres\Postgres;

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
         * @param string $action Action constant from Actions file
         * @param string $method HTTP request method
         * @param array $args Associative array of query args|params
         * @param string $body String with request body
         */
        public function __construct($config, $pg, $action, $method, $args, $body)
        {
            $this->config = $config;
            $this->pg = $pg;
            $this->action = $action;
            $this->method = $method;
            $this->args = $args;
            $this->body = $body;
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
    }

}

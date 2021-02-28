<?php

/*
ORM for Postgres
 */

namespace Postgres {

    // import all models
    require_once "Models/Model.php";
    require_once "Models/PersonPriz01.php";

    use ErrorException;

    class Postgres
    {
        /**
         * DataBase connection
         * @var resource
         */
        private $db;

        /**
         * Postgres constructor.
         * @param resource $db DataBase connection
         */
        public function __construct($db)
        {
            $this->db = $db;
        }

        /**
         * Query helper
         * @param string $query SQL query
         * @param array $params List of SQL query params ($1, $2, etc...)
         * @return array List of associative arrays
         * @throws ErrorException
         */
        public function query($query, $params = [])
        {
            // fetch all rows into associative array list
            $rows = [];
            $res = pg_query_params($this->db, $query, $params);
            if ($res === false) throw new ErrorException(
                "postgres query error: " . pg_last_error($this->db), 1
            );
            while ($row = pg_fetch_array($res, null, PGSQL_ASSOC)) {
                $mapped = [];
                foreach ($row as $name => $val) {
                    $mapped[$name] = $val;
                }
                $rows[] = $mapped;
            }
            return $rows;
        }

        /**
         * Escape string to safe inserting into query
         * @param string $str
         * @return string
         */
        public function escape($str)
        {
            return pg_escape_string($this->db, $str);
        }
    }

}

<?php

namespace SQLite {

    use SQLite3;
    use Postgres\Postgres;
    use ErrorException;

    class SQLite
    {
        /**
         * Local DataBase connection
         * @var SQLite3
         */
        private $db;

        /**
         * Main DataBase connection
         * @var Postgres
         */
        private $pg;

        /**
         * SQLite constructor.
         * @param SQLite3 $db DataBase connection
         * @param Postgres $pg Main DataBase
         * @throws ErrorException
         */
        public function __construct($db, $pg)
        {
            $this->db = $db;
            $this->pg = $pg;

            // DB upgrades flow
            $upgrades = [
                "1" => function () {
                    // Create persons table
                    $this->query("
                        CREATE TABLE persons (
                            p001 INT UNIQUE,
                            command TEXT DEFAULT '',
                            comment TEXT DEFAULT ''
                        );
                    ");
                },
                "2" => function() {
                    // Copy all commands & comments from main table to local
                    // Get all rows for users who have not empty extra field
                    $res = $this->pg->query("SELECT p001, p100 FROM priz10 WHERE p100 IS NOT NULL");
                    // Iterate over every person
                    foreach ($res as $row) {
                        // Parse extra field
                        $personID = $row["p001"];
                        $extra = explode("*", $row["p100"]);
                        // Modify extra array to be sure that it has 2 elements
                        if (empty($extra)) $extra = ["", ""];
                        if (count($extra) === 1) $extra = ["", $extra[0]];
                        $command = $this->escape($extra[0]);
                        $comment = $this->escape($extra[1]);
                        // Write command & comment to local DB
                        $this->query("
                            INSERT INTO persons (p001, command, comment)
                            VALUES ($personID, '$command', '$comment');
                        ");
                    }
                }
            ];

            // Get current version of DB
            $dbVer = (string)(int)$this->storeGet("version");

            // Start DB upgrading to current version
            foreach ($upgrades as $ver => $upgrade) {
                if ($dbVer < $ver) {
                    $upgrade();
                    $this->storeSet("version", $ver);
                }
            }

        }

        /**
         * Query helper
         * @param string $query SQL query
         * @return array List of associative arrays
         * @throws ErrorException
         */
        public function query($query)
        {
            // fetch all rows into associative array list
            $rows = [];
            $res = $this->db->query($query);
            if ($res == false) throw new ErrorException(
                "sqlite query error: " . $this->db->lastErrorMsg(), 1
            );
            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
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
            return SQLite3::escapeString($str);
        }

        /**
         * Tyring set value into store
         * @param string $key
         * @param string $val
         * @throws ErrorException
         */
        public function storeSet($key, $val)
        {
            // Create table if not exist
            $this->query("
                CREATE TABLE IF NOT EXISTS store (
                    key TEXT NOT NULL PRIMARY KEY,
                    value TEXT                   
                );                
            ");

            // Trying to add new row with key or update existing
            $eVal = $this->escape($val);
            try {
                $this->query("INSERT INTO store(key, value) VALUES ('$key', '$eVal');");
            } catch (ErrorException $e) {
                $this->query("UPDATE store SET value = '$eVal' WHERE key = '$key'");
            }
        }

        /**
         * Trying get value from store
         * @param string $key
         * @return string
         * @throws ErrorException
         */
        public function storeGet($key)
        {
            // Check that table exists
            $res = $this->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'store';");
            // If doesn't exist
            if (count($res) == 0) return "";
            // Else trying to get value
            $res = $this->query("SELECT value FROM store WHERE key = '$key';");
            if (count($res) == 1) return "" . $res[0]["value"];
            return "";
        }
    }

}

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
                    // Set additional params into store
                    $this->storeSet("made_for", "Пензенский областной сборный пункт");
                    $this->storeSet("developer", "Кулагин Юрий [@jkulvich]");
                    $this->storeSet("dev_email", "jkulvichi@gmail.com");

                    // Create people table
                    $this->query("
                        CREATE TABLE people (
                            p001 INT PRIMARY KEY,
                            command TEXT DEFAULT '',
                            comment TEXT DEFAULT ''
                        );
                    ");
                },
                "2" => function () {
                    // Copy all commands & comments from main table to local
                    // Get all rows for users who hasn't empty extra field
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
                        if (!empty(trim($command)) || !empty(trim($comment))) {
                            $this->query("
                                INSERT INTO people (p001, command, comment)
                                VALUES ($personID, '$command', '$comment');
                            ");
                        }
                    }
                },
                "3" => function () {
                    // Extend table to add "special" column for highlighting in UI
                    $this->query("ALTER TABLE people ADD special bool NOT NULL DEFAULT false");
                },
                "4" => function () {
                    // Create dictionary table with recruit offices' aliases
                    $this->query("
                        CREATE TABLE dict_recruit_offices (
                            id TEXT PRIMARY KEY,
                            num INT UNIQUE,
                            name TEXT
                        );
                    ");
                    // Fill aliases
                    $this->query("
                        INSERT INTO dict_recruit_offices (num, id, name) VALUES
                        (1, '08489495', 'Октябрьский и Железнодорожный'),
                        (2, '08489526', 'Первомайский и Ленинский'),
                        (3, '08489992', 'г.Кузнецк, Кузнецкий и Сосновоборский'),
                        (4, '08489561', 'Башмаковский и Пачелмский'),
                        (5, '08489696', 'Белинский и Тамалинский'),
                        (6, '08489650', 'Бессоновский и Мокшанский'),
                        (7, '08489733', 'Городищенский и Никольский'),
                        (8, '08489673', 'г. Заречный'),
                        (9, '08489785', 'Земетчинский и Вадинский'),
                        (10,'08489851', 'Каменский'),
                        (11,'08489940', 'Колышлейский и М.Сердобинский'),
                        (12,'08490015', 'Лунинский и Иссинский'),
                        (13,'08490050', 'Неверкинский и Камешкирский'),
                        (14, '08490133', 'Н.Ломовский и Наровчатский Спасский'),
                        (15, '08490328', 'Пензенский'),
                        (16, '08490334', 'Сердобский и Бековский'),
                        (17, '08490392', 'Шемышейский и Лопатинский')
                    ");
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
         * Returns last error code & desc
         * @return string
         */
        public function lastError()
        {
            $errCode = $this->db->lastErrorCode();
            $errDesc = $this->db->lastErrorMsg();
            return "$errCode: $errDesc";
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

        // /////////////////////// //
        // LOCAL KEY-VALUE STORAGE //
        // /////////////////////// //

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

        // /////////////////////////// //
        // DB FREQUENTLY USING ACTIONS //
        // /////////////////////////// //

        /**
         * Returns recruit office alias by ID
         * @param string $id ID of recruit office
         * @return string
         * @throws ErrorException
         */
        public function getRecruitOfficeAlias($id)
        {
            $vID = $this->escape($id);
            $res = $this->query("SELECT name FROM dict_recruit_offices WHERE id = '$vID'");
            if (count($res) === 1) return "" . $res[0]["name"];
            return "";
        }

        /**
         * Returns list of recruit offices aliases
         * @return array
         * @throws ErrorException
         */
        public function getRecruitOfficeAliases()
        {
            return $this->query("SELECT * FROM dict_recruit_offices ORDER BY num");
        }

    }

}

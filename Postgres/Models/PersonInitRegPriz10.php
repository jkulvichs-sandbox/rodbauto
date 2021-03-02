<?php

namespace Models {

    use ErrorException;
    use Postgres\Postgres;

    /**
     * Class PersonInitRegPriz10 for table priz10
     * ППГВУ - Первоначальная постановка граждан на воинский учёт
     * @package Models
     */
    class PersonInitRegPriz10 extends Model
    {
        /**
         * PersonInitRegPriz10 constructor.
         * @param Postgres $postgres
         */
        public function __construct($postgres)
        {
            parent::__construct($postgres);
        }

        /**
         * Table name to bind
         * @return string
         */
        protected function getTable()
        {
            return "priz10";
        }

        /**
         * DB fields to class fields map
         * @return array
         */
        protected function getMap()
        {
            return [
                "p001" => &$this->id,
                "p100" => &$this->extra,
            ];
        }

        /**
         * Find and set this model by ID
         * @param string $id Unique ID
         * @return PersonInitRegPriz10
         * @throws ErrorException
         */
        public function get($id)
        {
            $eID = $this->escape($id);
            return $this->getBy($this, "p001 = '$eID'");
        }

        /** Find all records by partial local command
         * @param string $localCommand Local command
         * @param string $where Additional statements
         * @param string $limit Max count of records
         * @param int $offset Records result offset
         * @return array Array of records that's match
         * @throws ErrorException
         */
        public function findAllByLocalCommand($localCommand, $where = "", $limit = "ALL", $offset = 0)
        {
            $eLocalCommand = $this->escape("$localCommand");
            print("LOCAL COMMAND\n\n");
            return $this->select(
                $this,
                "p100 ILIKE '%\"localCommand\": \"$eLocalCommand\"%'" . (empty($where) ? "" : " AND ($where)"),
                $limit,
                $offset
            );
        }

        /**
         * ID (p001)
         * @var string
         */
        public $id;

        /**
         * Extra JSON fields contains local command and description
         * @var string
         */
        public $extra;

        /**
         * Set extra field
         * @param mixed $data Mixed object to serialize into mixed field
         */
        public function setExtra($data)
        {
            if (empty($data)) {
                $this->extra = null;
            } else {
                $this->extra = json_encode($data);
            }
        }

        /**
         * Get extra field as a json
         * @return mixed Extra data
         */
        public function getExtra()
        {
            if ($this->extra) {
                return json_decode($this->extra, true);
            } else {
                return [];
            }
        }

    }

}

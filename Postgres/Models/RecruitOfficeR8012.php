<?php

namespace Models {

    use ErrorException;
    use Postgres\Postgres;

    /**
     * Class RecruitOfficeR8012 for table r8012
     * @package Models
     */
    class RecruitOfficeR8012 extends Model
    {
        /**
         * RecruitOfficeR8012 constructor.
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
            return "r8012";
        }

        /**
         * DB fields to class fields map
         * @return array
         */
        protected function getMap()
        {
            return [
                "p00" => &$this->id,
                "p01" => &$this->name,
            ];
        }

        /**
         * Find and set this model by ID
         * @param string $id Unique ID
         * @return RecruitOfficeR8012
         * @throws ErrorException
         */
        public function get($id)
        {
            $eID = $this->escape($id);
            return $this->getBy($this, "p00 = '$eID'");
        }

        /** Find all records by partial name of office
         * @param string $name Partial name of office
         * @param string $where Additional statements
         * @param string $limit Max count of records
         * @param int $offset Records result offset
         * @return array Array of records that's match
         * @throws ErrorException
         */
        public function findAllByName($name, $where = "", $limit = "ALL", $offset = 0)
        {
            $eName = $this->escape($name);
            return $this->select(
                $this,
                "p01 ILIKE '%$eName%'" . (empty($where) ? "" : " AND ($where)"),
                $limit,
                $offset
            );
        }

        /**
         * ID (p00)
         * @var string
         */
        public $id;

        /**
         * Name of office (p01)
         * @var string
         */
        public $name;

    }

}

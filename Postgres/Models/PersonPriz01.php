<?php

namespace Models {

    use ErrorException;
    use Postgres\Postgres;

    /**
     * Class Person for table priz01
     * @package Models
     */
    class PersonPriz01 extends Model
    {
        /**
         * PersonPriz01 constructor.
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
            return "priz01";
        }

        /**
         * DB fields to class fields map
         * @return array
         */
        protected function getMap()
        {
            return [
                "p001" => &$this->id,
                "pnom" => &$this->num,
                "p006" => &$this->firstName,
                "p005" => &$this->lastName,
                "p007" => &$this->middleName,
                "p169" => &$this->address,
                "p054" => &$this->personalIDSerial,
                "p055" => &$this->personalIDNumber,
                "cat_vu" => &$this->drivingLicenses,
                "k101" => &$this->birthYear,
                "p008" => &$this->passportSerial,
                "p099" => &$this->passportNumber,
            ];
        }

        /**
         * Find and set this model by ID
         * @param string $id Unique ID
         * @return PersonPriz01
         * @throws ErrorException
         */
        public function get($id)
        {
            $eID = $this->escape($id);
            return $this->getBy($this, "p001 = '$eID'");
        }

        /**
         * Find all records by partial full name
         * @param string $fullName Full name separated by whitespace
         * @param string $where Additional statements
         * @param string $limit Max count of records
         * @param int $offset Records result offset
         * @return array Array of records that's match
         * @throws ErrorException
         */
        public function findAllByFullName($fullName, $where = "", $limit = "ALL", $offset = 0)
        {
            $eFullName = $this->escape($fullName);
            return $this->select(
                $this,
                "(p005 || ' ' || p006 || ' ' || p007) ILIKE '%$eFullName%'" . (empty($where) ? "" : " AND ($where)"),
                $limit,
                $offset
            );
        }

        /**
         * Find all records by partial birth year
         * @param string $birthYear Partial birth year. Allowed: dddd and dd and other
         * @param string $where Additional statements
         * @param string $limit Max count of records
         * @param int $offset Records result offset
         * @return array Array of records that's match
         * @throws ErrorException
         */
        public function findAllByBirthYear($birthYear, $where = "", $limit = "ALL", $offset = 0)
        {
            $eBirthYear = $this->escape($birthYear);
            return $this->select(
                $this,
                "cast(k101 AS text) ILIKE '%$eBirthYear%'" . (empty($where) ? "" : " AND ($where)"),
                $limit,
                $offset
            );
        }

        /** Find all records by partial personal ID
         * @param string $personalID Partial personal ID with chars and numbers
         * @param string $where Additional statements
         * @param string $limit Max count of records
         * @param int $offset Records result offset
         * @return array Array of records that's match
         * @throws ErrorException
         */
        public function findAllByPersonalID($personalID, $where = "", $limit = "ALL", $offset = 0)
        {
            $ePersonalID = $this->escape($personalID);
            return $this->select(
                $this,
                "(p054 || '-' || p055) ILIKE '%$ePersonalID%'" . (empty($where) ? "" : " AND ($where)"),
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
         * Personal number (pnom)
         * @var string
         */
        public $num;

        /**
         * First name (p006)
         * @var string
         */
        public $firstName;

        /**
         * Last name (p005)
         * @var string
         */
        public $lastName;

        /**
         * Middle name (p007)
         * @var string
         */
        public $middleName;

        /**
         * Location address (p169)
         * @var string
         */
        public $address;

        /**
         * Personal ID serial (p054)
         * @var string
         */
        public $personalIDSerial;

        /**
         * Personal ID number (p055)
         * @var string
         */
        public $personalIDNumber;

        /**
         * Driving licenses as a string array (cat_vu)
         * @var array
         */
        public $drivingLicenses;

        /**
         * Year of birth (k101)
         * @var string
         */
        public $birthYear;

        /**
         * Passport serial (p008)
         * @var string
         */
        public $passportSerial;

        /**
         * Passport number (p099)
         * @var string
         */
        public $passportNumber;
    }

}

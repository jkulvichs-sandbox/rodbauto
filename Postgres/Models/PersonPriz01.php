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
                "p096" => &$this->passportSerial,
                "p013" => &$this->passportNumber,
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
         * Passport serial (p096)
         * @var string
         */
        public $passportSerial;

        /**
         * Passport number (p013)
         * @var string
         */
        public $passportNumber;
    }

}

<?php

namespace Structures {

    use Models\PersonInitRegPriz10;
    use Models\PersonPriz01;

    /**
     * Class PersonCardReply with info about person
     * @package Structures
     */
    class PersonCardReply
    {
        /**
         * Model for main person data
         * @var PersonPriz01
         */
        public $person;

        /**
         * Model for initial personal data
         * @var PersonInitRegPriz10
         */
        public $initReg;
    }

}

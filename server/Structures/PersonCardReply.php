<?php

namespace Structures {

    use Models\PersonInitRegPriz10;
    use Models\PersonPriz01;
    use Models\RecruitOfficeR8012;

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

        /**
         * Model for recruit office
         * @var RecruitOfficeR8012
         */
        public $recruitOffice;
    }

}

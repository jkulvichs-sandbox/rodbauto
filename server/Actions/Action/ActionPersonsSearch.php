<?php

namespace Action {

    use Actions\Context;
    use Actions\Response;
    use ErrorException;
    use Exception;
    use Models\PersonInitRegPriz10;
    use Models\PersonPriz01;
    use Models\RecruitOfficeR8012;
    use Structures\PersonCardReply;

    /**
     * Class ActionPersonsSearch to search persons
     * @package Actions
     */
    class ActionPersonsSearch extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         * @throws ErrorException
         */
        public function Execute($ctx)
        {
            switch ($ctx->method) {
                case "GET":
                    $this->ExecuteGET($ctx);
                    break;
                default:
                    (new Response())->AddError(405, "incorrect_method")->AddContext($ctx)->Reply();
            }

        }

        /**
         * GET request actions
         * @param Context $ctx Request context
         * @throws ErrorException
         */
        private function ExecuteGET($ctx)
        {
            // Filters
            $filterName = empty($ctx->args["name"]) ? "" : $ctx->pg->escape($ctx->args["name"]);
            $filterBirthYear = empty($ctx->args["birthYear"]) ? "" : $ctx->pg->escape($ctx->args["birthYear"]);
            $filterRecruitOffice = empty($ctx->args["recruitOffice"]) ? "" : $ctx->pg->escape($ctx->args["recruitOffice"]);
            $filterPersonalID = empty($ctx->args["personalID"]) ? "" : $ctx->pg->escape($ctx->args["personalID"]);
            $filterLocalCommand = empty($ctx->args["localCommand"]) ? "" : $ctx->pg->escape($ctx->args["localCommand"]);
            $filterLocalCommandNotEmpty = !empty($ctx->args["localCommandNotEmpty"]);

            // results limit per each results type
            $limit = empty($ctx->args["limit"]) ? 30000 : $ctx->pg->escape($ctx->args["limit"]);

            // Generating query for recruit office
            $sqlRecruitOffice = "AND rec_office_id ILIKE '$filterRecruitOffice'";
            if (empty($filterRecruitOffice)) $sqlRecruitOffice = "";

            // Generating personal id statement
            $sqlPersonalID = "AND personal_id ILIKE '%$filterPersonalID%'";
            if (empty($filterPersonalID)) $sqlPersonalID = "";

            // Generating query for local command
            $sqlLocalCommandQuery = "AND extra ILIKE '$filterLocalCommand*%'";
            if (empty($filterLocalCommand)) $sqlLocalCommandQuery = "";

            // SQL query template
            $sql = "
                SELECT *
                FROM (
                         SELECT person.p001::text                                       AS id,
                                person.p005 || ' ' || person.p006 || ' ' || person.p007 AS person_name,
                                person.k101::text                                       AS person_birth_year,
                                person.k001::text                                       AS person_birth,
                                (SELECT nom_li FROM gsp01_ur WHERE person.pnom = pnom)  AS personal_id,
                                rec_office.p01                                          AS rec_office_name,
                                rec_office.p00                                          AS rec_office_id,
                                init_reg.p100::text                                     AS extra
                         FROM priz01 as person
                                  JOIN priz10 as init_reg
                                       ON person.p001 = init_reg.p001
                                  JOIN r8012 as rec_office
                                       ON substr(person.pnom, 0, 9) = rec_office.p00                                  
                     ) AS card
                WHERE 
                      person_name ILIKE '%$filterName%'
                      AND person_birth_year ILIKE '%$filterBirthYear%'
                      $sqlRecruitOffice                
                      $sqlPersonalID
                      $sqlLocalCommandQuery
                ORDER BY person_name
                LIMIT $limit;
            ";

            // SQL query
            $cards = $ctx->pg->query($sql);

            // Responses repack
            $result = [];
            foreach ($cards as $card) {
                $extra = explode("*", $card["extra"]);
                // If extra is NULL create new with empty fields
                if (empty($extra)) $extra = ["", ""];
                if (count($extra) === 1) $extra = ["", $extra[0]];

                // Create a new card
                $newCard = [
                    "id" => $card["id"],
                    "name" => $card["person_name"],
                    "birthYear" => $card["person_birth_year"],
                    "birth" => date("d.m.Y", strtotime($card["person_birth"])),
                    "personalID" => $card["personal_id"],
                    "recruitOfficeName" => $ctx->sqlite->getRecruitOfficeAlias($card["rec_office_id"]),
                    "recruitOfficeID" => $card["rec_office_id"],
                    "extra" => ["localCommand" => $extra[0], "comment" => $extra[1]],
                ];

                if ($filterLocalCommandNotEmpty) {
                    if (strlen($newCard["extra"]["localCommand"]) > 0) {
                        $result[] = $newCard;
                    }
                } else {
                    $result[] = $newCard;
                }
            }

            (new Response($result))->Reply();
        }

        /**
         * Find filtered rows in main DB
         * @param Context $ctx
         * @param string $name
         * @param int $birthYear
         * @param int $recruitOffice
         * @param string $personalID
         * @return array
         * @throws ErrorException
         */
        function findMainDB($ctx, $name, $birthYear, $recruitOffice, $personalID)
        {
            $fName = empty($name) ? "" : $ctx->pg->escape($name);
            $fBirthYear = empty($birthYear) ? "" : $ctx->pg->escape($birthYear);
            $fRecruitOffice = empty($recruitOffice) ? "" : $ctx->pg->escape($recruitOffice);
            $fPersonalID = empty($personalID) ? "" : $ctx->pg->escape($personalID);

            // Generating query for recruit office
            $sqlRecruitOffice = "AND rec_office_id ILIKE '$fRecruitOffice'";
            if (empty($filterRecruitOffice)) $sqlRecruitOffice = "";

            // Generating personal id statement
            $sqlPersonalID = "AND personal_id ILIKE '%$fPersonalID%'";
            if (empty($filterPersonalID)) $sqlPersonalID = "";

            // SQL query template
            $sql = "
                SELECT *
                FROM (
                         SELECT person.p001::text                                       AS id,
                                person.p005 || ' ' || person.p006 || ' ' || person.p007 AS person_name,
                                person.k101::text                                       AS person_birth_year,
                                person.k001::text                                       AS person_birth,
                                (SELECT nom_li FROM gsp01_ur WHERE person.pnom = pnom)  AS personal_id,
                                rec_office.p01                                          AS rec_office_name,
                                rec_office.p00                                          AS rec_office_id,                                
                         FROM priz01 as person
                                  JOIN priz10 as init_reg
                                       ON person.p001 = init_reg.p001
                                  JOIN r8012 as rec_office
                                       ON substr(person.pnom, 0, 9) = rec_office.p00                                  
                     ) AS card
                WHERE 
                      person_name ILIKE '%$fName%'
                      AND person_birth_year ILIKE '%$fBirthYear%'
                      $sqlRecruitOffice                
                      $sqlPersonalID                      
                ORDER BY person_name;                
            ";

            // SQL query
            $cards = $ctx->pg->query($sql);

            // Responses repack
            $result = [];
            foreach ($cards as $card) {
                // Create a new card
                $newCard = [
                    "id" => $card["id"],
                    "name" => $card["person_name"],
                    "birthYear" => $card["person_birth_year"],
                    "birth" => date("d.m.Y", strtotime($card["person_birth"])),
                    "personalID" => $card["personal_id"],
                    "recruitOfficeName" => $ctx->sqlite->getRecruitOfficeAlias($card["rec_office_id"]),
                    "recruitOfficeID" => $card["rec_office_id"],
                ];
                $result[] = $newCard;
            }

            return $result;
        }

        /**
         * Find filtered rows in local DB
         * @param Context $ctx
         * @param string $localCommand
         * @param bool $localCommandNotEmpty
         * @param bool $special
         */
        function findLocalDB($ctx, $localCommand, $localCommandNotEmpty, $special)
        {
            $fLocalCommand = empty($localCommand) ? "" : $ctx->pg->escape($localCommand);
            $fLocalCommandNotEmpty = !empty($localCommandNotEmpty);
            $fSpecial = !empty($special);

            // Constrain local command filter
            $sqlLocalCommand = empty($fLocalCommand) ? "" : "command LIKE '$fLocalCommand'";

            // COnstrain local command not empty filter
            $sqlLocalCommandNotEmpty = empty($fLocalCommandNotEmpty) ? "" : "AND command";

            // SQL query template
            $sql = "
                SELECT * FROM persons WHERE
                    $sqlLocalCommand
            ";

            // SQL query
            $cards = $ctx->sqlite->query($sql);

            // Responses repack
            $result = [];
            foreach ($cards as $card) {
                // Create a new card
                $newCard = [
                    "id" => $card["p001"],
                    "command" => $card["command"],
                    "comment" => $card["comment"],
                    "special" => (bool)$card["special"],
                ];
                $result[] = $newCard;
            }

            return $result;
        }
    }

}

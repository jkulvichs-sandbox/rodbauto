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
            // Filters for main DB
            $fName = $ctx->args["name"];
            $fBirthYear = $ctx->args["birthYear"];
            $fRecruitOffice = $ctx->args["recruitOffice"];
            $fPersonalID = $ctx->args["personalID"];

            // Is any filter enabled for main DB
            $fMainDBEnabled = !empty($fName) || !empty($fBirthYear) || !empty($fRecruitOffice) || !empty($fPersonalID);

            // Filters for local DB
            $fLocalCommand = $ctx->args["localCommand"];
            $fLocalCommandNotEmpty = $ctx->args["localCommandNotEmpty"];
            $fSpecial = $ctx->args["special"];

            // Is any filter enabled for local DB
            $fLocalDBEnabled = !empty($fLocalCommand) || !empty($fLocalCommandNotEmpty) || !empty($fSpecial);

            // Main DB filters' group
            $mainFilters = [
                "name" => $fName,
                "birthYear" => $fBirthYear,
                "recruitOffice" => $fRecruitOffice,
                "personalID" => $fPersonalID
            ];

            // Local DB filters' group
            $localFilters = [
                "localCommand" => $fLocalCommand,
                "localCommandNotEmpty" => $fLocalCommandNotEmpty,
                "special" => $fSpecial
            ];

            // Flow to decide which behaviour will be applied
            // Depends on selected filters' group
            $cardsTotal = [];
            if (!$fLocalDBEnabled) {
                // Local DB filters are disabled. Main DB filters are optional.
                // Fetch all data from main DB, then fetch all IDs from main for local DB to merge.

                // Get all records satisfying the filter from main DB
                $cardsMain = $this->filterMainDB($ctx, $mainFilters);
                $cardsMainIDs = $this->cardsIDs($cardsMain);
                // Get only specified IDs from main
                $cardsLocal = $this->filterLocalDB($ctx, ["IDs" => $cardsMainIDs]);
                // Merge main & local data
                $cardsTotal = $this->mergeTotalCardArray($cardsMain, $cardsLocal);
            } else if (!$fMainDBEnabled) {
                // Main DB filters are disabled. Local DB filters are optional.
                // Fetch all data from local DB, then all IDs from local for main to merge.

                // Get all records satisfying the filter from local DB
                $cardsLocal = $this->filterLocalDB($ctx, $localFilters);
                $cardsLocalIDs = $this->cardsIDs($cardsLocal);
                // Get only specified IDs from local
                $cardsMain = $this->filterMainDB($ctx, ["IDs" => $cardsLocalIDs]);

                // Merge local & main data
                $cardsTotal = $this->mergeTotalCardArray($cardsLocal, $cardsMain);
            } else {
                // Both groups of filters are activated
                // Fetch data from main DB and local DB then find intersections on ID
                // then merge with reducing by intersection

                // Get all records satisfying filters
                $cardsMain = $this->filterMainDB($ctx, $mainFilters);
                $cardsLocal = $this->filterLocalDB($ctx, $localFilters);

                $cardsTotal = $this->mergeTotalCardArray($cardsMain, $cardsLocal, true);
            }

            (new Response($cardsTotal))->AddContext($ctx)->Reply();
        }

        /**
         * Find filtered rows in main DB
         * @param Context $ctx
         * @param array $filters
         * @return array
         * @throws ErrorException
         */
        function filterMainDB($ctx, $filters)
        {
            $fName = empty($filters["name"]) ? "" : $ctx->pg->escape($filters["name"]);
            $fBirthYear = empty($filters["birthYear"]) ? "" : $ctx->pg->escape($filters["birthYear"]);
            $fRecruitOffice = empty($filters["recruitOffice"]) ? "" : $ctx->pg->escape($filters["recruitOffice"]);
            $fPersonalID = empty($filters["personalID"]) ? "" : $ctx->pg->escape($filters["personalID"]);
            $fIDs = empty($filters["IDs"]) ? [] : array_map(function ($id) {
                return "'$id'";
            }, $filters["IDs"]);

            // Generating query for recruit office
            $sqlRecruitOffice = "AND rec_office_id ILIKE '$fRecruitOffice'";
            if (empty($fRecruitOffice)) $sqlRecruitOffice = "";

            // Generating personal id statement
            $sqlPersonalID = "AND personal_id ILIKE '%$fPersonalID%'";
            if (empty($fPersonalID)) $sqlPersonalID = "";

            // Generating IDs statement for searching only in specific set of people
            $sqlIDs = "AND id IN (" . join(", ", $fIDs) . ")";
            if (empty($fIDs)) $sqlIDs = "";

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
                                rec_office.p00                                          AS rec_office_id                                
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
                      $sqlIDs
                ORDER BY person_name;                
            ";

            // SQL query
            $rows = $ctx->pg->query($sql);

            return $this->rowsToCards($ctx, $rows, "main");
        }

        /**
         * Find filtered rows in local DB
         * @param Context $ctx
         * @param array $filters
         * @throws ErrorException
         */
        function filterLocalDB($ctx, $filters)
        {
            $fLocalCommand = empty($filters["localCommand"])
                ? []
                : array_map(function ($cmd) use ($ctx) {
                    $eCmd = $ctx->sqlite->escape(trim($cmd));
                    return "'$eCmd'";
                },
                    explode(",", $filters["localCommand"])
                );
            $fLocalCommandNotEmpty = !empty($filters["localCommandNotEmpty"]);
            $fSpecial = !empty($filters["special"]);
            $fIDs = empty($filters["IDs"]) ? [] : $filters["IDs"];

            // Constrain local command filter
            $sqlLocalCommand = empty($fLocalCommand) ? "" : "AND trim(command) IN (" . join(", ", $fLocalCommand) . ")";

            // Constrain local command not empty filter
            $sqlLocalCommandNotEmpty = empty($fLocalCommandNotEmpty) ? "" : "AND trim(command) NOT LIKE ''";

            // Constrain special flag filter
            $sqlSpecial = empty($fSpecial) ? "" : "AND special = true";

            // Constrain IDs filter to search only in specified set of people
            $sqlIDs = empty($fIDs) ? "" : "AND p001 IN (" . join(", ", $fIDs) . ")";

            // SQL query template
            $sql = "
                SELECT * FROM people WHERE
                    p001 IS NOT NULL
                    $sqlLocalCommand
                    $sqlLocalCommandNotEmpty
                    $sqlSpecial
                    $sqlIDs
            ";

            // SQL query
            $rows = $ctx->sqlite->query($sql);

            return $this->rowsToCards($ctx, $rows, "local");
        }

        /**
         * Merges local & main card with same id to total card with all data
         * @param $card1
         * @param $card2
         * @return array
         * @throws ErrorException
         */
        function mergeTotalCard($card1, $card2)
        {
            $cardLocal = $card1["_source"] == "local" ? $card1 : $card2;
            $cardMain = $card1["_source"] == "main" ? $card1 : $card2;

            // Some assertions to prevent most common logic wrongs
            if ($cardLocal == $cardMain)
                throw new ErrorException("can't merge cards with same source or _total source");
            if ($cardLocal["id"] != $cardMain["id"])
                throw new ErrorException("can't merge cards with different id");

            return [
                "_source" => "total",
                "id" => $cardLocal["id"],
                "name" => $cardMain["name"],
                "birthYear" => $cardMain["birthYear"],
                "birth" => $cardMain["birth"],
                "personalID" => $cardMain["personalID"],
                "recruitOfficeName" => $cardMain["recruitOfficeName"],
                "recruitOfficeID" => $cardMain["recruitOfficeID"],
                "command" => $cardLocal["command"],
                "comment" => $cardLocal["comment"],
                "special" => $cardLocal["special"],
            ];
        }

        /**
         * Merges main cards & related cards with same id by iteration through main cards.
         * Or leave main card as is if no related card there.
         * @param array $cards
         * @param array $relatedCards
         * @param bool $intersection if enabled - cards without pair card in related will be not included
         * @return array
         * @throws ErrorException
         */
        function mergeTotalCardArray($cards, $relatedCards, $intersection = false)
        {
            // Find short & long array for more quickly iteration
            $shortCards = $cards;
            $longCards = $relatedCards;
            if (count($relatedCards) < count($cards)) {
                $shortCards = $relatedCards;
                $longCards = $cards;
            }

            // Make associative cards' arrays to speed up processing
            $assocRelatedCards = $this->cardsToAssoc($longCards);

            // Merge cards or leave they as is if no related cards
            $totalCards = [];
            foreach ($shortCards as $card) {
                $relatedCard = $assocRelatedCards[$card["id"]];
                if ($relatedCard) {
                    $totalCards[] = $this->mergeTotalCard($card, $relatedCard);
                } else {
                    if (!$intersection) {
                        $totalCards[] = $card;
                    }
                }
            }

            return $totalCards;
        }

        /**
         * Converts DB result rows into cards
         * @param Context $ctx
         * @param array $rows
         * @return array
         * @throws ErrorException
         */
        function rowsToCards($ctx, $rows, $source)
        {
            // Responses repack
            $result = [];
            foreach ($rows as $card) {
                // Create a new card
                $newCard = [
                    "_source" => $source,
                    "id" => empty($card["id"]) ? $card["p001"] : $card["id"],
                    "name" => $card["person_name"],
                    "birthYear" => $card["person_birth_year"],
                    "birth" => date("d.m.Y", strtotime($card["person_birth"])),
                    "personalID" => $card["personal_id"],
                    "recruitOfficeName" => $ctx->sqlite->getRecruitOfficeAlias($card["rec_office_id"]),
                    "recruitOfficeID" => $card["rec_office_id"],
                    "command" => $card["command"],
                    "comment" => $card["comment"],
                    "special" => !empty($card["special"] && $card["special"] != "false"),
                ];
                $result[] = $newCard;
                $ctx->log($card["special"]);
            }

            return $result;
        }

        /**
         * Returns associative array from simple cards' array
         * @param array $cards
         * @return array
         */
        function cardsToAssoc($cards)
        {
            $assoc = [];
            foreach ($cards as $card) {
                $assoc[$card["id"]] = $card;
            }
            return $assoc;
        }

        /**
         * Returns array of cards' ids
         * @param array $cards
         * @return array
         */
        function cardsIDs($cards)
        {
            $ids = [];
            foreach ($cards as $card) $ids[] = $card["id"];
            return $ids;
        }
    }

}

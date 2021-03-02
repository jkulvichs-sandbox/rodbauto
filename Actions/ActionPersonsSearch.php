<?php

namespace Actions {

    use ErrorException;
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
            // power search query
            $query = $ctx->args["query"];
            // results limit per each results type
            $limit = $ctx->args["limit"];

            // exit if empty query
            if (empty($query)) {
                (new Response())
                    ->AddError(400, "empty_query", "add query param to initiate search")
                    ->AddContext($ctx)
                    ->Reply();
                return;
            }

            // limit of PG responses models of each type
            $pgLimit = empty($limit) ? 5 : min($limit, 10);

            // searching for full name
            $personsByFullName = (new PersonPriz01($ctx->pg))->findAllByFullName($query, "", $pgLimit);
            $replyByFullNames = [];
            for ($i = 0; $i < count($personsByFullName); $i++) {
                $card = new PersonCardReply();
                $card->person = $personsByFullName[$i];
                // searching for related models
                $card->initReg = (new PersonInitRegPriz10($ctx->pg))->get($card->person->id);
                $card->recruitOffice = (new RecruitOfficeR8012($ctx->pg))->get(substr($card->person->num, 0, 8));
                $replyByFullNames[] = $card;
            }

            // searching for birth year
            $personsByBirthYear = (new PersonPriz01($ctx->pg))->findAllByBirthYear($query, "", $pgLimit);
            $replyByBirthYear = [];
            for ($i = 0; $i < count($personsByBirthYear); $i++) {
                $card = new PersonCardReply();
                $card->person = $personsByBirthYear[$i];
                // searching for related models
                $card->initReg = (new PersonInitRegPriz10($ctx->pg))->get($card->person->id);
                $card->recruitOffice = (new RecruitOfficeR8012($ctx->pg))->get(substr($card->person->num, 0, 8));
                $replyByBirthYear[] = $card;
            }

            // searching for personal ID
            $personsByPersonalID = (new PersonPriz01($ctx->pg))->findAllByPersonalID($query, "", $pgLimit);
            $replyByPersonalID = [];
            for ($i = 0; $i < count($personsByPersonalID); $i++) {
                $card = new PersonCardReply();
                $card->person = $personsByPersonalID[$i];
                // searching for related models
                $card->initReg = (new PersonInitRegPriz10($ctx->pg))->get($card->person->id);
                $card->recruitOffice = (new RecruitOfficeR8012($ctx->pg))->get(substr($card->person->num, 0, 8));
                $replyByPersonalID[] = $card;
            }

            // searching for local command
            $initRegsByLocalCommand = (new PersonInitRegPriz10($ctx->pg))->findAllByLocalCommand($query, "", $pgLimit);
            $replyByLocalCommand = [];
            for ($i = 0; $i < count($initRegsByLocalCommand); $i++) {
                $card = new PersonCardReply();
                $card->initReg = $initRegsByLocalCommand[$i];
                // searching for related models
                $card->person = (new PersonPriz01($ctx->pg))->get($card->initReg->id);
                $card->recruitOffice = (new RecruitOfficeR8012($ctx->pg))->get(substr($card->person->num, 0, 8));
                $replyByLocalCommand[] = $card;
            }

            // searching for recruiting office name
            $recOfficesByName = (new RecruitOfficeR8012($ctx->pg))->findAllByName($query, "", $pgLimit);
            $replyByRecOfficeName = [];
            foreach ($recOfficesByName as $recOffice) {
                $replyByRecOfficeName[$recOffice->name] = [];
                //TODO: Здесь ограничить фильтрами
                $persons = (new PersonPriz01($ctx->pg))->findAllByRecruitOfficeID($recOffice->id, "");
                for ($i = 0; $i < count($persons); $i++) {
                    $card = new PersonCardReply();
                    $card->person = $persons[$i];
                    $card->recruitOffice = $recOffice;
                    // searching for related models
                    $card->initReg = (new PersonInitRegPriz10($ctx->pg))->get($card->person->id);
                    $replyByRecOfficeName[$recOffice->name][] = $card;
                }
            }

            // make response model
            $resp = [
                "query" => $query,
                "fullName" => $replyByFullNames,
                "birthYear" => $replyByBirthYear,
                "personalID" => $replyByPersonalID,
                "localCommand" => $replyByLocalCommand,
                "recruitOffice" => $replyByRecOfficeName,
            ];

            (new Response($resp))->Reply();
        }
    }

}

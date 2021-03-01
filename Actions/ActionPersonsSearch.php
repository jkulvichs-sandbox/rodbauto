<?php

namespace Actions {

    use ErrorException;
    use Models\PersonPriz01;

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
            $query = $ctx->args["query"];

            // exit if empty query
            if (empty($query)) {
                (new Response())
                    ->AddError(404, "empty_query", "add query param to initiate search")
                    ->AddContext($ctx)
                    ->Reply();
                return;
            }

            // limit of PG responses models of each type
            $pgLimit = 10;

            // searching
            $pgResFullName = (new PersonPriz01($ctx->pg))->findAllByFullName($query, "", $pgLimit);
            $pgResBirthYear = (new PersonPriz01($ctx->pg))->findAllByBirthYear($query, "", $pgLimit);
            $pgResPersonalID = (new PersonPriz01($ctx->pg))->findAllByPersonalID($query, "", $pgLimit);

            // make response model
            $resp = [
                "fullName" => $pgResFullName,
                "birthYear" => $pgResBirthYear,
                "personalID" => $pgResPersonalID,
            ];

            (new Response($resp))->Reply();
        }
    }

}

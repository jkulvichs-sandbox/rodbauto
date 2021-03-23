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
     * Class ActionUpdateExtra to update person's extra info
     * @package Actions
     */
    class ActionUpdateExtra extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         * @throws ErrorException
         */
        public function Execute($ctx)
        {
            switch ($ctx->method) {
                case "PUT":
                    $this->ExecutePUT($ctx);
                    break;
                default:
                    (new Response())->AddError(405, "incorrect_method")->AddContext($ctx)->Reply();
            }

        }

        /**
         * PUT request actions
         * @param Context $ctx Request context
         * @throws ErrorException
         */
        private function ExecutePUT($ctx)
        {
            // Filters
            $personID = $ctx->pg->escape($ctx->args["id"]);
            $extra = json_decode($ctx->body, true);

            $extraComment = $ctx->pg->escape($extra["comment"]);
            $extraLocalCommand = $ctx->pg->escape($extra["localCommand"]);

            $extraString = "$extraLocalCommand*$extraComment";

            // SQL query template
            $sql = "
                UPDATE priz10 SET p100 = '$extraString' WHERE p001 = '$personID';
            ";

            // SQL query
            $ctx->pg->query($sql);

            (new Response($extra))->Reply();
        }
    }

}

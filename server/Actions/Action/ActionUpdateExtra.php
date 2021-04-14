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
            $personID = (int)$ctx->sqlite->escape($ctx->args["id"]);
            $extra = json_decode($ctx->body, true);

            $extraComment = $ctx->sqlite->escape($extra["comment"]);
            $extraLocalCommand = $ctx->sqlite->escape($extra["localCommand"]);
            $extraSpecial = (int)$ctx->sqlite->escape($extra["special"]);

            // Try to insert new data
            try {
                $ctx->sqlite->query("
                    INSERT INTO people (p001, command, comment, special)
                    VALUES ('$personID', '$extraLocalCommand', '$extraComment', $extraSpecial);
                ");
            } catch (ErrorException $e) {
                // Otherwise try to add new row
                $ctx->sqlite->query("
                    UPDATE people SET 
                        command = '$extraLocalCommand',
                        comment = '$extraComment',
                        special = $extraSpecial
                    WHERE p001 = $personID
                ");
            }

            (new Response($extra))->Reply();
        }
    }

}

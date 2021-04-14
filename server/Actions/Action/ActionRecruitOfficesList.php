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
     * Class ActionRecruitOfficesList to manipulate with list of recruit offices
     * @package Actions
     */
    class ActionRecruitOfficesList extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         * @throws ErrorException
         */
        public function Execute($ctx)
        {
            //TODO: Switch sets based on region in query
            switch ($ctx->method) {
                case "GET":
                    (new Response($ctx->sqlite->getRecruitOfficeAliases()))->Reply();
                    break;
                default:
                    (new Response())->AddError(405, "incorrect_method")->AddContext($ctx)->Reply();
            }

        }
    }

}

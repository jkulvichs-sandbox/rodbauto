<?php

namespace Actions {

    /**
     * Class ActionPersonsSearch to search persons
     * @package Actions
     */
    class ActionPersonsSearch extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         */
        public function Execute($ctx)
        {
            switch ($ctx->method) {
                case "GET":
                    (new Response("OK"))->Reply();
                    break;
                default:
                    (new Response())->AddError(405, "incorrect_method")->AddContext($ctx)->Reply();
            }

        }
    }

}

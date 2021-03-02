<?php

namespace Actions {

    /**
     * Class ActionError to reply error
     * @package Actions
     */
    class ActionError extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         */
        public function Execute($ctx)
        {
            (new Response())->AddError(400)->AddContext($ctx)->Reply();
        }

        /**
         * @param int $httpCode HTTP response code
         * @param string $code Error code
         * @param string $message Error message
         * @param Context $ctx Request context
         */
        public function ExecuteError($httpCode, $code, $message, $ctx = null)
        {
            $errResp = (new Response())->AddError($httpCode, $code, $message);
            if ($ctx !== null) $errResp->AddContext($ctx);
            $errResp->Reply();
        }
    }

}

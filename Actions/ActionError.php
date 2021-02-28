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
            print((new Response())->AddError()->AddContext($ctx));
        }

        /**
         * @param string $code Error code
         * @param string $message Error message
         * @param Context $ctx Request context
         */
        public function ExecuteError($code, $message, $ctx = null)
        {
            $errResp = (new Response())->AddError($code, $message);
            if ($ctx !== null) $errResp->AddContext($ctx);
            print($errResp);
        }
    }

}

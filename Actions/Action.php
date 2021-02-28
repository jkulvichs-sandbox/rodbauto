<?php

namespace Actions {

    /**
     * Class Action to define other actions.
     * All actions must inherits from this class.
     * @package Actions
     */
    abstract class Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed
         */
        public abstract function Execute($ctx);
    }

}

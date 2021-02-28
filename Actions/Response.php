<?php

namespace Actions {

    /**
     * Class Response to unification servers responses
     * @package Actions
     */
    class Response
    {
        public function __toString()
        {
            return json_encode($this, JSON_PRETTY_PRINT);
        }

        /**
         * Mixed response body
         * @var mixed
         */
        public $body;

        /**
         * Response constructor.
         * @param mixed $body
         */
        public function __construct($body = null)
        {
            $this->body = $body;
        }

        /**
         * Error section
         * @var array
         */
        public $error = [
            "status" => false,
            "code" => "",
            "message" => "",
        ];

        /**
         * Mark tis response as an error response
         * @param string $code Error code
         * @param string $message Description of the error
         * @return Response
         */
        public function AddError($code = "common", $message = "")
        {
            $this->error["status"] = true;
            $this->error["code"] = $code;
            $this->error["message"] = $message;
            return $this;
        }

        /**
         * Response context
         * @var array
         */
        public $context = [
            "action" => "",
            "method" => "",
            "args" => "",
            "body" => "",
        ];

        /**
         * Add context info to this reply
         * @param Context $ctx
         * @return Response
         */
        public function AddContext($ctx)
        {
            $this->context["action"] = $ctx->action;
            $this->context["method"] = $ctx->method;
            $this->context["args"] = $ctx->args;
            $this->context["body"] = $ctx->body;
            return $this;
        }
    }

}

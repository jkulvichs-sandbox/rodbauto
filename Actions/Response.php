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
         * Send response to client
         */
        public function Reply()
        {
            http_response_code($this->httpStatusCode);
            header('Content-type: application/json');
            print($this);
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
         * @var int HTTP status code to return
         */
        private $httpStatusCode = 200;

        /**
         * Mark tis response as an error response
         * @param int $httpCode HTTP status code
         * @param string $code Error code
         * @param string $message Description of the error
         * @return Response
         */
        public function AddError($httpCode, $code = "common", $message = "")
        {
            $this->httpStatusCode = $httpCode;
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

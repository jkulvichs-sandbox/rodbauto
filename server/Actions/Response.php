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
            return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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
         * Mixed response data
         * @var mixed
         */
        public $data;

        /**
         * Response constructor.
         * @param mixed $data
         */
        public function __construct($data = null)
        {
            $this->data = $data;
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
            "postgre_error" => "",
            "sqlite_error" => "",
            "log" => []
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
            $this->context["sqlite_error"] = $ctx->sqlite->lastError();
            $this->context["postgre_error"] = $ctx->pg->lastError();
            $this->context["log"] = $ctx->logHistory;
            return $this;
        }
    }

}

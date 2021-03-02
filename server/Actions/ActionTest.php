<?php

namespace Actions {

    /**
     * Class ActionEcho to echo all request inputs and check service availability
     * @package Actions
     */
    class ActionTest extends Action
    {
        /**
         * @param Context $ctx Request context
         * @return mixed|void
         */
        public function Execute($ctx)
        {
            $args = json_encode($ctx->args);

            print("
<title>RODBAuto Test Page</title>

<div id='header'>
    <span>RODBAuto Backend Test</span>
</div>

<div id='info'>    
    <div class='line'><strong>ACTION:</strong> {$ctx->action}</div>
    <div class='line'><strong>METHOD:</strong> {$ctx->method}</div>
    <div class='line'><strong>ARGS:</strong> {$args}</div>
    <div class='line'><strong>BODY:</strong> {$ctx->body}</div>
</div>

<style>
  body {
    font-family: sans-serif;    
    margin: 0;        
  }    
  
  #header {
    font-size: xx-large;
    text-align: center;
    background: #3949AB;
    color: #FFFFFF;
    padding: 10px;
  }        
  
  #info {    
    background: #C5CAE9;
    color: #000000;
    padding: 10px;
  } 
  
  #info .line {
    padding: 4px;
  } 
</style>
            ");
        }
    }

}

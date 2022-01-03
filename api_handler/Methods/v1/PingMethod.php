<?php

    namespace Methods\v1;

    use KimchiAPI\Objects\Response;

    class PingMethod extends \KimchiAPI\Abstracts\Method
    {
        public function execute(): Response
        {
            $response = new Response();
            $response->ResultData = ['Foo' => 'Bar'];
            return $response;
        }
    }
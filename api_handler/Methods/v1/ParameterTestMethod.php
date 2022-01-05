<?php

    namespace Methods\v1;

    use KimchiAPI\Classes\Request;
    use KimchiAPI\Objects\Response;

    class ParameterTestMethod extends \KimchiAPI\Abstracts\Method
    {
        public function execute(): Response
        {
            $response = new Response();
            $response->ResultData = Request::getParameters();
            return $response;
        }
    }
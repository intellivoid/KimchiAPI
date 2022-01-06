<?php

    namespace Methods\v1;

    use KimchiAPI\Abstracts\Method;
    use KimchiAPI\Exceptions\AccessKeyNotProvidedException;
    use KimchiAPI\KimchiAPI;
    use KimchiAPI\Objects\Response;

    class AuthenticationTestMethod extends Method
    {
        /**
         * @throws AccessKeyNotProvidedException
         */
        public function execute(): Response
        {
            $response = new Response();
            $response->ResultData = KimchiAPI::getAuthenticationToken();
            return $response;
        }
    }
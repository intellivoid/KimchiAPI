<?php

    namespace Methods\v1;

    use KimchiAPI\Abstracts\Method;
    use KimchiAPI\Exceptions\AccessKeyNotProvidedException;
    use KimchiAPI\Exceptions\AuthenticationNotProvidedException;
    use KimchiAPI\KimchiAPI;
    use KimchiAPI\Objects\Response;

    class UserAuthenticationTestMethod extends Method
    {
        /**
         * @return Response
         * @throws AuthenticationNotProvidedException
         */
        public function execute(): Response
        {
            $response = new Response();
            $response->ResultData = KimchiAPI::getUserAuthentication()->toArray();
            return $response;
        }
    }
<?php

    namespace KimchiAPI\Objects\ResponseStandards;

    use KimchiAPI\Objects\Response;
    use KimchiAPI\Utilities\Converter;

    class GoogleAPI implements \KimchiAPI\Interfaces\ResponseStandardInterface
    {

        /**
         * @inheritDoc
         */
        public static function convertToResponseStandard(Response $response): array
        {
            if($response->Success)
            {
                return [
                    'data' => $response->ResultData
                ];
            }

            if(defined('KIMCHI_API_DEBUGGING_MODE') && KIMCHI_API_DEBUGGING_MODE)
            {
                return [
                    'error' => [
                        'code' => $response->ErrorCode,
                        'message' => $response->ErrorMessage,
                        'exception' => ($response->Exception == null ? null : Converter::exceptionToArray($response->Exception))
                    ]
                ];
            }

            return [
                'error' => [
                    'code' => $response->ErrorCode,
                    'message' => $response->ErrorMessage
                ]
            ];
        }
    }
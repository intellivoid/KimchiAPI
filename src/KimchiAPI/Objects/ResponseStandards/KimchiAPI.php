<?php

    namespace KimchiAPI\Objects\ResponseStandards;

    use KimchiAPI\Objects\Response;
    use KimchiAPI\Utilities\Converter;

    class KimchiAPI implements \KimchiAPI\Interfaces\ResponseStandardInterface
    {

        /**
         * @inheritDoc
         */
        public static function convertToResponseStandard(Response $response): array
        {
            if($response->Success)
            {
                return [
                    'status' => true,
                    'request_id' => KIMCHI_API_REQUEST_ID,
                    'result' => $response->ResultData
                ];
            }
            else
            {
                if(defined('KIMCHI_API_DEBUGGING_MODE') && KIMCHI_API_DEBUGGING_MODE)
                {
                    return [
                        'status' => false,
                        'request_id' => KIMCHI_API_REQUEST_ID,
                        'error_code' => $response->ErrorCode,
                        'description' => $response->ErrorMessage,
                        'exception' => ($response->Exception == null ? null : Converter::exceptionToArray($response->Exception))
                    ];
                }

                return [
                    'status' => false,
                    'request_id' => KIMCHI_API_REQUEST_ID,
                    'error_code' => $response->ErrorCode,
                    'description' => $response->ErrorMessage
                ];
            }
        }
    }
<?php

    namespace KimchiAPI\Objects\ResponseStandards;

    use KimchiAPI\Interfaces\ResponseStandardInterface;
    use KimchiAPI\Objects\Response;
    use KimchiAPI\Utilities\Converter;

    class IntellivoidAPI implements ResponseStandardInterface
    {

        /**
         * @inheritDoc
         */
        public static function convertToResponseStandard(Response $response): array
        {
            if($response->Success)
            {
                return [
                    'success' => true,
                    'response_code' => (int)$response->ResponseCode,
                    'results' => $response->ResultData
                ];
            }

            if(defined('KIMCHI_API_DEBUGGING_MODE') && KIMCHI_API_DEBUGGING_MODE)
            {
                return [
                    'success' => false,
                    'response' => (int)$response->ResponseCode,
                    'error' => [
                        'error_code' => $response->ErrorCode,
                        'type' => 'NOT_APPLICABLE (BACKWARDS COMPATIBILITY)',
                        'message' => $response->ErrorMessage
                    ],
                    'exception' => ($response->Exception == null ? null : Converter::exceptionToArray($response->Exception))
                ];
            }

            return [
                'success' => false,
                'response_code' => (int)$response->ResponseCode,
                'error' => [
                    'error_code' => $response->ErrorCode,
                    'type' => 'NOT_APPLICABLE (BACKWARDS COMPATIBILITY)',
                    'message' => $response->ErrorMessage
                ]
            ];
        }
    }
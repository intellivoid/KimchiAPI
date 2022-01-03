<?php

    namespace KimchiAPI\Objects\ResponseStandards;

    use KimchiAPI\Interfaces\ResponseStandardInterface;
    use KimchiAPI\Objects\Response;
    use KimchiAPI\Utilities\Converter;

    class JsonApiOrg implements ResponseStandardInterface
    {
        /**
         * @inheritDoc
         */
        public static function convertToResponseStandard(Response $response): array
        {
            if($response->Success)
            {
                return [
                    'data' => [$response->ResultData]
                ];
            }

            if(defined('KIMCHI_API_DEBUGGING_MODE') && KIMCHI_API_DEBUGGING_MODE)
            {
                return [
                    'errors' => [
                        [
                            'status' => $response->ErrorCode,
                            'detail' => $response->ErrorMessage,
                            'source' => ($response->Exception == null ? null : Converter::exceptionToArray($response->Exception))
                        ]
                    ]
                ];
            }

            return [
                'errors' => [
                    [
                        'status' => $response->ErrorCode,
                        'detail' => $response->ErrorMessage,
                    ]
                ]
            ];
        }
    }
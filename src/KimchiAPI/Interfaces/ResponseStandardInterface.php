<?php

    namespace KimchiAPI\Interfaces;

    use KimchiAPI\Objects\Response;

    interface ResponseStandardInterface
    {
        /**
         * Converts the response to a response standard
         *
         * @param Response $response
         * @return array
         */
        public static function convertToResponseStandard(Response $response): array;
    }
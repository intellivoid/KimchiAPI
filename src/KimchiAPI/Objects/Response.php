<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI\Objects;

    use Exception;
    use KimchiAPI\Abstracts\ResponseStandard;
    use KimchiAPI\Abstracts\ResponseType;
    use KimchiAPI\Utilities\Converter;

    class Response
    {
        /**
         * Indicates if the response was successful or not
         *
         * @var bool
         */
        public $Success;

        /**
         * The code of the error if an error is to be raised
         *
         * @var int|null
         */
        public $ErrorCode;

        /**
         * The message of the error if an error is to be raised
         *
         * @var string|null
         */
        public $ErrorMessage;

        /**
         * The result data of the request
         *
         * @var array|string|int|bool|null
         */
        public $ResultData;

        /**
         * @var string|ResponseStandard
         */
        public $ResponseStandard;

        /**
         * The HTTP response code to return
         *
         * @var int
         */
        public $ResponseCode;

        /**
         * The response type to return to the client
         *
         * @var string|ResponseType
         */
        public $ResponseType;

        /**
         * Custom HTTP headers to return
         *
         * @var array
         */
        public $Headers;

        /**
         * Optional exception for debugging purposes
         *
         * @var Exception|null
         */
        public $Exception;

        public function __construct()
        {
            $this->Success = true;
            $this->ErrorCode = null;
            $this->ResponseStandard = ResponseStandard::KimchiAPI;
            $this->ResultData = null;
            $this->ResponseCode = 200;
            $this->ResponseType = ResponseType::Automatic;
            $this->Headers = [];
            $this->Exception = null;
        }

        /**
         * Returns an array representation of the object
         *
         * @param bool $compact Returns a compact representation for ZiProto serialization
         * @return array
         */
        public function toArray(bool $compact=false): array
        {
            return [
                'success' => $this->Success,
                'error_code' => $this->ErrorCode,
                'error_message' => $this->ErrorMessage,
                'result_data' => $this->ResultData,
                'response_standard' => $this->ResponseStandard,
                'response_code' => $this->ResponseCode,
                'response_type' => $this->ResponseType,
                'headers' => $this->Headers,
                'exception' => ($this->Exception == null ? null : Converter::exceptionToArray($this->Exception))
            ];
        }

        /**
         * Constructs a response from an exception
         * @param Exception $exception
         * @param bool $internal_error
         * @return Response
         */
        public static function fromException(Exception $exception, bool $internal_error=false): Response
        {
            $response_object = new Response();
            $response_object->Success = false;
            $response_object->Exception = $exception;

            if($internal_error)
            {
                $response_object->ErrorCode = 0;
                $response_object->ErrorMessage = 'There was an internal server error';
            }
            else
            {
                $response_object->ErrorCode = $exception->getCode();
                $response_object->ErrorMessage = $exception->getMessage();
            }

            return $response_object;
        }
    }
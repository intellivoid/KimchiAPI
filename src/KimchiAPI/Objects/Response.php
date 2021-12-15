<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI\Objects;

    use Exception;

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
         * Optional information about the error in a data form
         *
         * @var array|string|int|bool|null
         */
        public $ErrorData;

        /**
         * The result data of the request
         *
         * @var array|string|int|bool|null
         */
        public $ResultData;

        /**
         * Returns an array representation of the object
         *
         * @param bool $compact Returns a compact representation for ZiProto serialization
         * @return array
         */
        public function toArray(bool $compact=false): array
        {
            if($compact)
            {
                return [
                    0x001 => $this->Success,
                    0x002 => $this->ErrorCode,
                    0x003 => $this->ErrorMessage,
                    0x004 => $this->ErrorData,
                    0x005 => $this->ResultData
                ];
            }
            else
            {
                return [
                    "success" => $this->Success,
                    "error_code" => $this->ErrorCode,
                    "error_message" => $this->ErrorMessage,
                    "error_data" => $this->ErrorData,
                    "result_data" => $this->ResultData
                ];
            }
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Response
         */
        public static function fromArray(array $data): Response
        {
            $response_object = new Response();

            if(isset($data[0x001]))
                $response_object->Success = $data[0x001];
            if(isset($data["success"]))
                $response_object->Success = $data["success"];

            if(isset($data[0x002]))
                $response_object->ErrorCode = $data[0x002];
            if(isset($data["error_code"]))
                $response_object->ErrorCode = $data["error_code"];

            if(isset($data[0x003]))
                $response_object->ErrorMessage = $data[0x003];
            if(isset($data["error_message"]))
                $response_object->ErrorMessage = $data["error_message"];

            if(isset($data[0x004]))
                $response_object->ErrorData = $data[0x004];
            if(isset($data["error_data"]))
                $response_object->ErrorData = $data["error_data"];

            if(isset($data[0x005]))
                $response_object->ResultData = $data[0x005];
            if(isset($data["result_data"]))
                $response_object->ResultData = $data["result_data"];

            return $response_object;
        }

        /**
         * Constructs a response from an exception
         * @param Exception $exception
         * @return Response
         */
        public static function fromException(Exception $exception): Response
        {
            $response_object = new Response();

            $response_object->Success = false;
            $response_object->ErrorCode = $exception->getCode();
            $response_object->ErrorMessage = $exception->getMessage();

            return $response_object;
        }
    }
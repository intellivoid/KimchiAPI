<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI\Objects;

    use KimchiAPI\Abstracts\RequestMethod;

    class Request
    {
        /**
         * The IP address of the client making the request
         *
         * @var string|null
         */
        public $ClientIP;

        /**
         * The version of the API that's being invoked
         *
         * @var string|null
         */
        public $ApiVersion;

        /**
         * The method that was being invoked
         *
         * @var string
         */
        public $Method;

        /**
         * An array of parameters, this option may not be available
         *
         * @var array|null
         */
        public $Parameters;

        /**
         * @var string|RequestMethod
         */
        public $RequestMethod;

        /**
         * Returns an array representation of the object
         *
         * @param bool $compact
         * @return array
         */
        public function toArray(bool $compact=false): array
        {
            if($compact)
            {
                return [
                    0x001 => $this->ClientIP,
                    0x002 => $this->ApiVersion,
                    0x003 => $this->Method,
                    0x004 => $this->Parameters,
                    0x005 => $this->RequestMethod
                ];
            }

            return [
                'client_ip' => $this->ClientIP,
                'api_version' => $this->ApiVersion,
                'method' => $this->Method,
                'parameters' => $this->Parameters,
                'request_method' => $this->RequestMethod
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return Request
         */
        public static function fromArray(array $data): Request
        {
            $request_object = new Request();

            if(isset($data[0x001]))
                $request_object->ClientIP = $data[0x001];
            if(isset($data['client_ip']))
                $request_object->ClientIP = $data['client_ip'];

            if(isset($data[0x002]))
                $request_object->ApiVersion = $data[0x002];
            if(isset($data['api_version']))
                $request_object->ApiVersion = $data['api_version'];

            if(isset($data[0x003]))
                $request_object->Method = $data[0x003];
            if(isset($data['method']))
                $request_object->Method = $data['method'];

            if(isset($data[0x004]))
                $request_object->Parameters = $data[0x004];
            if(isset($data['parameters']))
                $request_object->Parameters = $data['parameters'];

            if(isset($data[0x005]))
                $request_object->RequestMethod = $data[0x005];
            if(isset($data['request_method']))
                $request_object->RequestMethod = $data['request_method'];

            return $request_object;
        }
    }
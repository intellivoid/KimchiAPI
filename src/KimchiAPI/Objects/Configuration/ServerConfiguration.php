<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI\Objects\Configuration;

    class ServerConfiguration
    {
        /**
         * Indicates if logging is enabled or not
         *
         * @var bool
         */
        public $LoggingEnabled;

        /**
         * The root path of the API for routing purposes.
         *
         * @var string
         */
        public $RootPath;

        /**
         * Indicates if the framework signature headers are to be returned to the HTTP response
         *
         * @var bool
         */
        public $FrameworkSignature;

        /**
         * Indicates if the API Signature headers are to be returned to the HTTP response
         *
         * @var bool
         */
        public $ApiSignature;

        /**
         * An array of hard-coded headers to be returned to the HTTP response
         *
         * @var array
         */
        public $Headers;

        /**
         * Indicates if KHM is enabled for this API
         *
         * @var bool
         */
        public $KhmEnabled;

        /**
         * An array of flags to deny if KHM detects one
         *
         * @var array
         */
        public $FirewallDeny;

        /**
         * Returns an array representation of the object
         *
         * @return array
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function toArray(): array
        {
            return [
                'logging_enabled' => (bool)$this->LoggingEnabled,
                'root_path' => $this->RootPath,
                'framework_signature' => (bool)$this->FrameworkSignature,
                'api_signature' => (bool)$this->ApiSignature,
                'headers' => $this->Headers,
                'khm_enabled' => (bool)$this->KhmEnabled,
                'firewall_deny' => $this->FirewallDeny
            ];
        }

        /**
         * Constructs object from an array representation of the object
         *
         * @param array $data
         * @return ServerConfiguration
         */
        public static function fromArray(array $data): ServerConfiguration
        {
            $ServerConfigurationObject = new ServerConfiguration();

            if(isset($data['logging_enabled']))
                $ServerConfigurationObject->LoggingEnabled = $data['logging_enabled'];

            if(isset($data['root_path']))
                $ServerConfigurationObject->RootPath = $data['root_path'];

            if(isset($data['framework_signature']))
                $ServerConfigurationObject->FrameworkSignature = $data['framework_signature'];

            if(isset($data['api_signature']))
                $ServerConfigurationObject->ApiSignature = $data['api_signature'];

            if(isset($data['headers']))
                $ServerConfigurationObject->Headers = $data['headers'];

            if(isset($data['khm_enabled']))
                $ServerConfigurationObject->KhmEnabled = (bool)$data['khm_enabled'];

            if(isset($data['firewall_deny']))
                $ServerConfigurationObject->FirewallDeny = $data['firewall_deny'];

            return $ServerConfigurationObject;
        }
    }
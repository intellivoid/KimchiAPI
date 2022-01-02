<?php

    namespace KimchiAPI\Objects\Configuration;

    class VersionConfiguration
    {
        /**
         * The version name of the configuration
         *
         * @var string
         */
        public $Version;

        /**
         * Indicates if the version is enabled or not
         *
         * @var bool
         */
        public $Enabled;

        /**
         * @var MethodConfiguration[]
         */
        public $Methods;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $methods_array = [];
            foreach($this->Methods as $method)
                $methods_array[] = $method->toArray();

            return [
                'version' => $this->Version,
                'enabled' => (bool)$this->Enabled,
                'methods' => $methods_array
            ];
        }

        /**
         * Constructs object from an array representation of the object
         *
         * @param array $data
         * @return VersionConfiguration
         */
        public static function fromArray(array $data): VersionConfiguration
        {
            $version_configuration = new VersionConfiguration();

            if(isset($data['version']))
                $version_configuration->Version = $data['version'];

            if(isset($data['enabled']))
                $version_configuration->Enabled = (bool)$data['enabled'];

            if(isset($data['methods']))
            {
                foreach($data['methods'] as $method)
                    $version_configuration->Methods[] = MethodConfiguration::fromArray($method);
            }

            return $version_configuration;
        }
    }
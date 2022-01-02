<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI\Objects;

    use KimchiAPI\Objects\Configuration\ServerConfiguration;
    use KimchiAPI\Objects\Configuration\VersionConfiguration;

    class Configuration
    {
        /**
         * The name of the API service
         *
         * @var string
         */
        public $Name;

        /**
         * The configuration for the KimchiAPI server
         *
         * @var ServerConfiguration
         */
        public $ServerConfiguration;

        /**
         * An array of version configurations
         *
         * @var VersionConfiguration[]
         */
        public $Versions;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $versions = [];
            foreach($this->Versions as $version)
                $versions[] = $version->toArray();

            return [
                'name' => $this->Name,
                'configuration' => $this->ServerConfiguration->toArray(),
                'versions' => $versions
            ];
        }

        /**
         * Constructs object from an array configuration
         *
         * @param array $data
         * @return Configuration
         */
        public static function fromArray(array $data): Configuration
        {
            $configuration_object = new Configuration();

            if(isset($data['name']))
                $configuration_object->Name = $data['name'];

            if(isset($data['configuration']))
                $configuration_object->ServerConfiguration = ServerConfiguration::fromArray($data['configuration']);

            if(isset($data['versions']))
            {
                foreach($data['versions'] as $version)
                    $configuration_object->Versions[] = VersionConfiguration::fromArray($version);
            }

            return $configuration_object;
        }
    }
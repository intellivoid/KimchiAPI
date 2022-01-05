<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI\Classes;

    use khm\Exceptions\DatabaseException;
    use khm\khm;
    use KimchiAPI\Abstracts\Method;
    use KimchiAPI\Exceptions\ApiException;
    use KimchiAPI\Exceptions\ApiMethodNotFoundException;
    use KimchiAPI\Exceptions\ConnectionBlockedException;
    use KimchiAPI\Exceptions\InternalServerException;
    use KimchiAPI\Exceptions\IOException;
    use KimchiAPI\Exceptions\RouterException;
    use KimchiAPI\KimchiAPI;
    use KimchiAPI\Objects\Configuration;
    use KimchiAPI\Utilities\Client;
    use Symfony\Component\Uid\Uuid;

    class API
    {
        /**
         * @var Configuration
         */
        private $Configuration;

        /**
         * @var string
         */
        private $ResourcesPath;

        /**
         * @var string
         */
        private $ConfigurationFilePath;

        /**
         * @var Router
         */
        private $Router;

        /**
         * @param string $resources_path
         * @throws IOException
         * @throws InternalServerException
         */
        public function __construct(string $resources_path)
        {
            $this->ResourcesPath = $resources_path;
            $this->ConfigurationFilePath = $this->ResourcesPath . DIRECTORY_SEPARATOR . 'configuration.json';

            if(file_exists($this->ConfigurationFilePath) == false)
                throw new IOException('The API configuration file \'configuration.json\' does not exist');

            $DecodedConfiguration = json_decode(file_get_contents($this->ConfigurationFilePath), true);

            if($DecodedConfiguration == false)
                throw new InternalServerException('Cannot read configuration file, ' . json_last_error_msg());

            $this->Configuration = Configuration::fromArray($DecodedConfiguration);
            $this->Router = new Router();
        }

        /**
         * Initializes the Kimchi API server.
         *
         * @return void
         * @throws ApiException
         * @throws ConnectionBlockedException
         * @throws DatabaseException
         * @throws RouterException
         */
        public function initialize()
        {
            if(defined('KIMCHI_API_INITIALIZED'))
                throw new ApiException('Cannot initialize ' . $this->Configuration->Name . ', another API is already initialized');

            define('KIMCHI_API_RESOURCES_PATH', $this->ResourcesPath);
            define('KIMCHI_API_CONFIGURATION_PATH', $this->ConfigurationFilePath);
            define('KIMCHI_API_NAME', $this->Configuration->Name);
            define('KIMCHI_API_DEBUGGING_MODE', $this->Configuration->ServerConfiguration->DebuggingMode);
            define('KIMCHI_API_ROOT_PATH', $this->Configuration->ServerConfiguration->RootPath);
            define('KIMCHI_API_SIGNATURES', $this->Configuration->ServerConfiguration->ApiSignature);
            define('KIMCHI_API_FRAMEWORK_SIGNATURE', $this->Configuration->ServerConfiguration->FrameworkSignature);
            define('KIMCHI_API_LOGGING_ENABLED', $this->Configuration->ServerConfiguration->LoggingEnabled);
            define('KIMCHI_API_HEADERS', $this->Configuration->ServerConfiguration->Headers);
            define('KIMCHI_API_REQUEST_ID', Uuid::v1()->toRfc4122());

            $this->defineClientDefinitions();
            $this->defineRoutes();

            define('KIMCHI_API_INITIALIZED', 1);
        }

        /**
         * @return Configuration
         */
        public function getConfiguration(): Configuration
        {
            return $this->Configuration;
        }

        /**
         * @return string
         */
        public function getResourcesPath(): string
        {
            return $this->ResourcesPath;
        }

        /**
         * @return string
         */
        public function getConfigurationFilePath(): string
        {
            return $this->ConfigurationFilePath;
        }

        /**
         * @return Router
         */
        public function getRouter(): Router
        {
            return $this->Router;
        }

        /**
         * @return void
         * @throws RouterException
         */
        private function defineRoutes()
        {
            foreach($this->Configuration->Versions as $version)
            {
                foreach($version->Methods as $method)
                {
                    $full_path = '/' . $version->Version . '/' . $method->Path;
                    $this->Router->map(implode('|', $method->Methods), $full_path, function() use ($version, $method, $full_path)
                    {
                        if(class_exists($method->Class) == false)
                            throw new ApiMethodNotFoundException('API Method not found');

                        /** @var Method $method_class */
                        $method_class = new $method->Class;
                        KimchiAPI::handleResponse($method_class->execute());
                    }, $version->Version . '/' . $method->Class);
                }
            }
        }

        /**
         * @throws DatabaseException
         * @throws ConnectionBlockedException
         */
        private function defineClientDefinitions()
        {
            define('KIMCHI_CLIENT_IP_ADDRESS', Client::getClientIP());

            if($this->Configuration->ServerConfiguration->KhmEnabled)
            {
                $khm = new khm();
                $IdentifiedClient = $khm->identify();

                define('KIMCHI_KHM_ENABLED', true);
                define('KIMCHI_KHM_FIREWALL', $this->Configuration->ServerConfiguration->FirewallDeny);
                define('KIMCHI_KHM_FLAGS', $IdentifiedClient->Flags);

                foreach($this->Configuration->ServerConfiguration->FirewallDeny as $item)
                {
                    if(in_array($item, $IdentifiedClient->Flags))
                    {
                        throw new ConnectionBlockedException('Firewall block rule ' . $item);
                    }
                }
            }
            else
            {
                define('KIMCHI_KHM_ENABLED', false);
                define('KIMCHI_KHM_FIREWALL', null);
                define('KIMCHI_KHM_FLAGS', null);
            }
        }
    }
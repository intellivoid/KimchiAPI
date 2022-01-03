<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI;

    use Exception;
    use khm\Exceptions\DatabaseException;
    use KimchiAPI\Abstracts\Method;
    use KimchiAPI\Abstracts\ResponseStandard;
    use KimchiAPI\Abstracts\ResponseType;
    use KimchiAPI\Classes\API;
    use KimchiAPI\Exceptions\IOException;
    use KimchiAPI\Exceptions\MissingComponentsException;
    use KimchiAPI\Exceptions\UnsupportedResponseStandardException;
    use KimchiAPI\Objects\Response;
    use KimchiAPI\Objects\ResponseStandards\GoogleAPI;
    use KimchiAPI\Objects\ResponseStandards\IntellivoidAPI;
    use KimchiAPI\Objects\ResponseStandards\JsonApiOrg;
    use KimchiAPI\Utilities\Converter;
    use ppm\Exceptions\AutoloaderException;
    use ppm\Exceptions\InvalidComponentException;
    use ppm\Exceptions\InvalidPackageLockException;
    use ppm\Exceptions\PackageNotFoundException;
    use ppm\Exceptions\VersionNotFoundException;
    use ppm\ppm;
    use RuntimeException;

    // Define server information for response headers
    if(defined("KIMCHI_API_SERVER") == false)
    {
        if(file_exists(__DIR__ . DIRECTORY_SEPARATOR . "package.json") == false)
            throw new MissingComponentsException("The 'package.json' file was not found in the distribution");

        $package = json_decode(file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "package.json"), true);
        if($package == false)
            throw new RuntimeException("Cannot decode 'package.json', package components may be corrupted");

        define("KIMCHI_API_SERVER_VERSION", $package["package"]["version"]);
        define("KIMCHI_API_SERVER_ORGANIZATION", $package["package"]["organization"]);
        define("KIMCHI_API_SERVER_AUTHOR", $package["package"]["author"]);
        define("KIMCHI_API_SERVER", true);
    }


    class KimchiAPI
    {
        /**
         * @var array
         */
        private $commands_paths;

        /**
         * @var array
         */
        private $command_classes;

        /**
         * Server constructor.
         */
        public function __construct()
        {
            $this->commands_paths = [];
            $this->command_classes = [];
        }

        /**
         * Return the list of commands paths
         *
         * @return array
         */
        public function getCommandsPaths(): array
        {
            return $this->commands_paths;
        }

        /**
         * Return the list of command classes
         *
         * @return array
         */
        public function getCommandClasses(): array
        {
            return $this->command_classes;
        }


        /**
         * Add a single custom commands path
         *
         * @param string $path Custom commands' path to add
         * @param bool $before If the path should be prepended or appended to the list
         * @throws IOException
         */
        public function addCommandsPath(string $path, bool $before=true)
        {
            if (!is_dir($path))
            {
                throw new IOException('Method path "' . $path . '" does not exist.');
            }
            elseif (!in_array($path, $this->commands_paths, true))
            {
                if ($before)
                {
                    array_unshift($this->commands_paths, $path);
                }
                else
                {
                    $this->commands_paths[] = $path;
                }
            }
        }

        /**
         * Add multiple custom commands paths
         *
         * @param array $paths Custom commands paths to add
         * @param bool $before If the paths should be prepended or appended to the list
         * @throws IOException
         */
        public function addCommandsPaths(array $paths, bool $before=true)
        {
            foreach ($paths as $path)
            {
                $this->addCommandsPath($path, $before);
            }
        }


        /**
         * Get an object instance of the passed command
         *
         * @param string $command
         * @param string $filepath
         *
         * @return Method|null
         */
        public function getCommandObject(string $command, string $filepath = ''): ?Method
        {
            if (isset($this->commands_objects[$command]))
            {
                return $this->commands_objects[$command];
            }

            $which = [Method::AUTH_SYSTEM];
            $which[] = Method::AUTH_USER;

            foreach ($which as $auth)
            {
                $command_class = $this->getCommandClassName($auth, $command, $filepath);

                if ($command_class)
                {
                    return new $command_class();
                }
            }

            return null;
        }

        /**
         * Get classname of predefined commands
         *
         * @see command_classes
         *
         * @param string $auth     Auth of command
         * @param string $command  Command name
         * @param string $filepath Path to the command file
         *
         * @return string|null
         */
        public function getCommandClassName(string $auth, string $command, string $filepath = ''): ?string
        {
            $command = mb_strtolower($command);
            $auth = Converter::ucFirstUnicode($auth);

            // First, check for directly assigned command class.
            if ($command_class = $this->command_classes[$auth][$command] ?? null)
            {
                return $command_class;
            }

            // Start with default namespace.
            $command_namespace = __NAMESPACE__ . '\\Methods\\' . $auth . 'Methods';

            // Check if we can get the namespace from the file (if passed).
            if ($filepath && !($command_namespace = Converter::getFileNamespace($filepath)))
            {
                return null;
            }

            $command_class = $command_namespace . '\\' . Converter::ucFirstUnicode($command) . 'Method';

            if (class_exists($command_class))
            {
                return $command_class;
            }

            return null;
        }

        /**
         * @param string $package
         * @param bool $import_dependencies
         * @param bool $throw_error
         * @throws AutoloaderException
         * @throws Exceptions\ApiException
         * @throws Exceptions\ConnectionBlockedException
         * @throws Exceptions\InternalServerException
         * @throws IOException
         * @throws InvalidComponentException
         * @throws InvalidPackageLockException
         * @throws PackageNotFoundException
         * @throws VersionNotFoundException
         * @throws DatabaseException
         */
        public static function exec(string $package, bool $import_dependencies=true, bool $throw_error=true)
        {
            $decoded = explode('==', $package);
            if($decoded[1] == 'latest')
                $decoded[1] = ppm::getPackageLock()->getPackage($decoded[0])->getLatestVersion();
            $path = ppm::getPackageLock()->getPackage($decoded[0])->getPackagePath($decoded[1]); // Find the package path
            ppm::import($decoded[0], $decoded[1], $import_dependencies, $throw_error); // Import dependencies

            $API = new API($path);
            $API->initialize();
            self::handleRequest($API);
        }

        /**
         * Handles the request to the API
         *
         * @param API $API
         * @param string|null $requestUrl
         * @param string|null $requestMethod
         * @return void
         */
        public static function handleRequest(API $API, ?string $requestUrl=null, string $requestMethod = null)
        {
            $match = $API->getRouter()->match($requestUrl, $requestMethod);

            // call closure or throw 404 status
            if(is_array($match) && is_callable($match['target']))
            {
                try
                {
                    call_user_func_array($match['target'], array_values($match['params']));
                }
                catch(Exception $e)
                {
                    exit();
                }
            }
            else
            {
                print("404");
                exit();
            }
        }

        /**
         * Handles the response handler and returns the response data to the client
         *
         * @param Response $response
         * @throws Exceptions\UnsupportedResponseTypeExceptions
         * @throws UnsupportedResponseStandardException
         */
        public static function handleResponse(Response $response)
        {
            http_response_code($response->ResponseCode);

            if($response->ResponseType == ResponseType::Automatic)
                $response->ResponseType = ResponseType::Json;

            switch($response->ResponseStandard)
            {
                case ResponseStandard::GoogleAPI:
                    $response_data = GoogleAPI::convertToResponseStandard($response);
                    break;

                case ResponseStandard::IntellivoidAPI:
                    $response_data = IntellivoidAPI::convertToResponseStandard($response);
                    break;

                case ResponseStandard::JsonApiOrg:
                    $response_data = JsonApiOrg::convertToResponseStandard($response);
                    break;

                case ResponseStandard::KimchiAPI:
                    $response_data = Objects\ResponseStandards\KimchiAPI::convertToResponseStandard($response);
                    break;

                default:
                    throw new UnsupportedResponseStandardException('The response standard \'' . $response->ResponseStandard . '\' is not supported');
            }

            $return_results = Converter::serializeResponse($response_data, $response->ResponseType);
            http_response_code($response->ResponseCode);
            foreach($response->Headers as $header => $value)
                header("$header: $value");
            header('Content-Type: ' . $response->ResponseType);
            header('Content-Length: ' . strlen($return_results));
            print($return_results);
            exit();
        }
    }
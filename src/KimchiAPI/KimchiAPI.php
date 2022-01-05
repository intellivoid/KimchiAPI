<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI;

    use Exception;
    use khm\Exceptions\DatabaseException;
    use KimchiAPI\Abstracts\ResponseStandard;
    use KimchiAPI\Abstracts\ResponseType;
    use KimchiAPI\Classes\API;
    use KimchiAPI\Exceptions\ApiException;
    use KimchiAPI\Exceptions\ApiMethodNotFoundException;
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
    use Symfony\Component\Uid\Uuid;
    use VerboseAdventure\Abstracts\EventType;
    use VerboseAdventure\VerboseAdventure;

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
         * @var VerboseAdventure
         */
        private static $VerboseAdventure;

        /**
         * @param string $package
         * @param bool $import_dependencies
         * @param bool $throw_error
         * @throws AutoloaderException
         * @throws DatabaseException
         * @throws Exceptions\ApiException
         * @throws Exceptions\ConnectionBlockedException
         * @throws Exceptions\InternalServerException
         * @throws Exceptions\RouterException
         * @throws Exceptions\UnsupportedResponseTypeExceptions
         * @throws IOException
         * @throws InvalidComponentException
         * @throws InvalidPackageLockException
         * @throws PackageNotFoundException
         * @throws UnsupportedResponseStandardException
         * @throws VersionNotFoundException
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
         * Returns a VerboseAdventure instance, creates one if none exists.
         *
         * @return VerboseAdventure
         * @throws ApiException
         */
        public static function getVerboseAdventure(): VerboseAdventure
        {
            if(defined('KIMCHI_API_INITIALIZED') == false)
                throw new ApiException('The API Environment must be initialized before using VerboseAdventure');

            if(self::$VerboseAdventure == null)
                self::$VerboseAdventure = new VerboseAdventure(KIMCHI_API_NAME);

            return self::$VerboseAdventure;
        }

        /**
         * Handles the request to the API
         *
         * @param API $API
         * @param string|null $requestUrl
         * @param string|null $requestMethod
         * @return void
         * @throws Exceptions\UnsupportedResponseTypeExceptions
         * @throws UnsupportedResponseStandardException
         * @throws ApiException
         * @noinspection PhpIssetCanBeReplacedWithCoalesceInspection
         * @noinspection PhpRedundantCatchClauseInspection
         */
        public static function handleRequest(API $API, ?string $requestUrl=null, string $requestMethod = null)
        {
            // set Request Url if it isn't passed as parameter
            if($requestUrl === null)
            {
                $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            }

            // strip base path from request url
            $requestUrl = substr($requestUrl, strlen($API->getRouter()->getBasePath()));

            // Strip query string (?a=b) from Request Url
            /** @noinspection SpellCheckingInspection */
            if (($strpos = strpos($requestUrl, '?')) !== false)
            {
                $requestUrl = substr($requestUrl, 0, $strpos);
            }

            // set Request Method if it isn't passed as a parameter
            if($requestMethod === null)
            {
                $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            }

            define('KIMCHI_API_REQUEST_URL', $requestUrl);
            define('KIMCHI_API_REQUEST_METHOD', $requestMethod);

            $match = $API->getRouter()->match($requestUrl, $requestMethod);

            // call closure or throw 404 status
            if(is_array($match) && is_callable($match['target']))
            {
                try
                {
                    call_user_func_array($match['target'], array_values($match['params']));
                }
                catch(ApiMethodNotFoundException $e)
                {

                    unset($e);
                    self::handle404();
                }
                catch(Exception $e)
                {
                    self::getVerboseAdventure()->logException($e,  KIMCHI_API_REQUEST_ID);
                    self::handleException($e);
                }
            }

            self::handle404();
        }

        /**
         * Handles an exception response
         *
         * @param Exception $exception
         * @param string $response_standard
         * @param string $response_type
         * @return void
         * @throws ApiException
         * @throws Exceptions\UnsupportedResponseTypeExceptions
         * @throws UnsupportedResponseStandardException
         */
        public static function handleException(Exception $exception, string $response_standard=ResponseStandard::KimchiAPI, string $response_type=ResponseType::Json)
        {
            $response = new Response();
            $response->ResponseCode = 500;
            $response->Success = false;
            $response->ErrorCode = $exception->getCode();
            $response->ErrorMessage = 'There was an internal server error while trying to process your request';
            $response->Exception = $exception;
            $response->ResponseStandard = ResponseStandard::KimchiAPI;
            $response->ResponseType = ResponseType::Json;

            self::handleResponse($response);
        }

        /**
         * Returns a 404 response
         *
         * @param string $response_standard
         * @param string $response_type
         * @return void
         * @throws ApiException
         * @throws Exceptions\UnsupportedResponseTypeExceptions
         * @throws UnsupportedResponseStandardException
         */
        public static function handle404(string $response_standard = ResponseStandard::KimchiAPI, string $response_type = ResponseType::Json)
        {
            $response = new Response();
            $response->ResponseCode = 404;
            $response->Success = false;
            $response->ErrorCode = 404;
            $response->ErrorMessage = 'The requested resource/action is invalid or not found';
            $response->ResponseStandard = $response_standard;
            $response->ResponseType = $response_type;

            self::handleResponse($response);
        }

        /**
         * Returns the headers used for framework
         *
         * @return array
         */
        public static function getFrameworkHeaders(): array
        {
            return [
                'X-Organization' => KIMCHI_API_SERVER_ORGANIZATION,
                'X-Powered-By' => 'KimchiAPI/' . KIMCHI_API_SERVER_VERSION
            ];
        }

        /**
         * Returns the headers for the API
         *
         * @return array
         */
        public static function getApiHeaders(): array
        {
            return [
                'X-API' => KIMCHI_API_NAME
            ];
        }


        /**
         * Handles the response handler and returns the response data to the client
         *
         * @param Response $response
         * @throws Exceptions\UnsupportedResponseTypeExceptions
         * @throws UnsupportedResponseStandardException
         * @throws ApiException
         */
        public static function handleResponse(Response $response)
        {
            self::getVerboseAdventure()->log(EventType::INFO, KIMCHI_API_REQUEST_METHOD . ' ' . KIMCHI_API_REQUEST_URL . ' ' . $response->ResponseCode, KIMCHI_API_REQUEST_ID);
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
            if(defined('KIMCHI_API_FRAMEWORK_SIGNATURE') && KIMCHI_API_FRAMEWORK_SIGNATURE)
            {
                foreach(self::getFrameworkHeaders() as $header => $value)
                    header("$header: $value");
            }
            if(defined('KIMCHI_API_SIGNATURES') && KIMCHI_API_SIGNATURES)
            {
                foreach(self::getApiHeaders() as $header => $value)
                    header("$header: $value");
            }
            foreach($response->Headers as $header => $value)
                header("$header: $value");
            if(defined('KIMCHI_API_REQUEST_ID'))
            {
                header('X-Request-ID: ' . KIMCHI_API_REQUEST_ID);
            }
            header('Content-Type: ' . $response->ResponseType);
            header('Content-Length: ' . strlen($return_results));
            print($return_results);
            exit();
        }
    }
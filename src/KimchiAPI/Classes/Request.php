<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace KimchiAPI\Classes;

    use KimchiAPI\Abstracts\RequestMethod;

    class Request
    {
        /**
         * Defined dynamical parameters
         *
         * @var array|null
         */
        private static $definedDynamicParameters;

        /**
         * Returns the request method that was used
         *
         * @return string|RequestMethod
         * @noinspection PhpUnused
         */
        public static function getRequestMethod(): string
        {
            return strtoupper($_SERVER['REQUEST_METHOD']);
        }

        /**
         * Gets a POST parameter if it's set
         *
         * @param string $value
         * @return string|null
         */
        public static function getPostParameter(string $value): ?string
        {
            if(isset($_POST[$value]))
                return $_POST[$value];

            return null;
        }

        /**
         * Returns all POST parameters
         *
         * @return array
         */
        public static function getPostParameters(): array
        {
            return $_POST;
        }

        /**
         * Gets a GET parameter if it's set
         *
         * @param string $value
         * @return string|null
         */
        public static function getGetParameter(string $value): ?string
        {
            if(isset($_GET[$value]))
                return $_GET[$value];

            return null;
        }

        /**
         * Returns all the GET parameters
         *
         * @return array
         */
        public static function getGetParameters(): array
        {
            return $_GET;
        }

        /**
         * Returns a specified header otherwise null if not set
         *
         * @param string $value
         * @return string|null
         */
        public function getHeaderParameter(string $value): ?string
        {
            $headers = self::getHeaderParameters();
            if(isset($headers[$value]))
                return $headers[$value];
            return null;
        }

        /**
         * Returns an array of header parameters
         *
         * @return array
         */
        public static function getHeaderParameters(): array
        {
            if(function_exists('getallheaders'))
                return getallheaders();

            $headers = [];

            $copy_server = [
                'CONTENT_TYPE'   => 'Content-Type',
                'CONTENT_LENGTH' => 'Content-Length',
                'CONTENT_MD5'    => 'Content-Md5',
            ];

            foreach ($_SERVER as $key => $value)
            {
                if (substr($key, 0, 5) === 'HTTP_')
                {
                    $key = substr($key, 5);
                    if (!isset($copy_server[$key]) || !isset($_SERVER[$key]))
                    {
                        $key = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', $key))));
                        $headers[$key] = $value;
                    }
                }
                elseif (isset($copy_server[$key]))
                {
                    $headers[$copy_server[$key]] = $value;
                }
            }

            if (!isset($headers['Authorization']))
            {
                if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']))
                {
                    $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                }
                elseif (isset($_SERVER['PHP_AUTH_USER']))
                {
                    $basic_pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                    $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
                }
                elseif (isset($_SERVER['PHP_AUTH_DIGEST']))
                {
                    $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
                }
            }

            return $headers;
        }

        /**
         * Returns a POST/GET Parameter
         *
         * @param string $value
         * @return string|null
         * @noinspection PhpUnused
         */
        public static function getParameter(string $value): ?string
        {
            if(self::getGetParameter($value) !== null)
                return self::getGetParameter($value);

            if(self::getPostParameter($value) !== null)
                return self::getPostParameter($value);

            /** @noinspection PhpConditionAlreadyCheckedInspection */
            if(isset(self::getDefinedDynamicParameters()[$value]) !== null)
                return self::getDefinedDynamicParameters()[$value];

            return null;
        }

        /**
         * Returns all the parameters combined, could overwrite existing parameters
         *
         * @return array
         */
        public static function getParameters(): array
        {
            return array_merge(
                self::getHeaderParameters(),
                self::getGetParameters(),
                self::getPostParameters(),
                self::getDefinedDynamicParameters(),
                self::getPostBody()
            );
        }

        /**
         * Returns a defined Dynamical Parameter
         *
         * @param string $value
         * @return string|null
         * @noinspection PhpUnused
         */
        public static function getDefinedDynamicParameter(string $value): ?string
        {
            if(isset(self::getDefinedDynamicParameters()[$value]))
                return self::getDefinedDynamicParameters()[$value];

            return null;
        }

        /**
         * Define dynamical parameters
         *
         * @return array
         * @noinspection PhpUnused
         */
        public static function getDefinedDynamicParameters(): array
        {
            return (self::$definedDynamicParameters == null ? [] : self::$definedDynamicParameters);
        }

        /**
         * Sets the defined dynamical parameters
         *
         * @param array|null $definedDynamicParameters
         */
        public static function setDefinedDynamicParameters(?array $definedDynamicParameters): void
        {
            self::$definedDynamicParameters = $definedDynamicParameters;
        }

        /**
         * Returns the post body given by the client
         *
         * @return array
         */
        public static function getPostBody(): array
        {
            $results = @file_get_contents('php://input');

            if($results == false)
                $results = stream_get_contents(fopen('php://stdin', 'r'));

            if($results == false)
                return [];

            $decoded = json_decode($results, true);
            if($decoded == false)
                return [];
            return $decoded;
        }
    }
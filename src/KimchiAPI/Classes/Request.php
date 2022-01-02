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
                self::getGetParameters(),
                self::getPostParameters(),
                self::getDefinedDynamicParameters()
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
         * @return string|null
         */
        public static function getPostBody(): ?string
        {
            $results = @file_get_contents('php://input');

            if($results == false)
                $results = stream_get_contents(fopen('php://stdin', 'r'));

            IF($results == false)
                return null;

            return null;
        }
    }
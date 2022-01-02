<?php

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
         *
         *
         * @var bool
         */
        public $FrameworkSignature;

        public $ApiSignature;

        public $Headers;
    }
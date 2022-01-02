<?php

    namespace KimchiAPI\Abstracts;

    use KimchiAPI\KimchiAPI;
    use KimchiAPI\Objects\Request;

    abstract class Command
    {
        /**
         * Auth level for user commands
         */
        public const AUTH_USER = 'User';

        /**
         * Auth level for system commands
         */
        public const AUTH_SYSTEM = 'System';

        /**
         * Auth level for admin commands
         */
        public const AUTH_ADMIN = 'Admin';

        /**
         * KimchiAPI Object
         *
         * @var KimchiAPI
         */
        protected $KimchiAPI;

        /**
         * Request object
         *
         * @var Request
         */
        protected $Request;

        /**
         * The name of the method
         *
         * @var string
         */
        protected $Name;

        /**
         * A description of the method
         *
         * @var string
         */
        protected $Description;

        abstract public function execute();
    }
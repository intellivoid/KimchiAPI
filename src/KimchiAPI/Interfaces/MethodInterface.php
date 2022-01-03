<?php

    namespace KimchiAPI\Interfaces;

    use KimchiAPI\Objects\Request;
    use KimchiAPI\Objects\Response;

    interface MethodInterface
    {
        /**
         * Returns the name of the method
         *
         * @return string
         */
        public function getMethodName(): string;

        /**
         * Returns the path used to invoke the method
         *
         * @return string
         */
        public function getMethod(): string;

        /**
         * Gets a description of the method
         *
         * @return string
         */
        public function getDescription(): string;

        /**
         * Gets the version of the method
         *
         * @return string
         */
        public function getVersion(): string;

        /**
         * Executes the method by passing on the parameter
         *
         * @param Request $request
         * @return Response
         */
        public function execute(Request $request): Response;
    }
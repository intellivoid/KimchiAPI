<?php

    namespace KimchiAPI\Abstracts;

    abstract class Method
    {
        protected $KimchiAPI;

        protected $Request;

        protected $Name;

        protected $Description;

        protected $DocumentationURL;

        protected $Version;

        protected $Enabled;

        abstract public function execute();


    }
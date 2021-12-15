<?php

    namespace KimchiAPI;

    // Define server information for response headers
    use KimchiAPI\Exceptions\MissingComponentsException;
    use KimchiRPC\Abstracts\ServerMode;
    use KimchiRPC\Abstracts\Types\ProtocolType;
    use KimchiRPC\Utilities\Converter;
    use RuntimeException;

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
         * Server constructor.
         * @param string $server_name
         */
        public function __construct(string $server_name)
        {
            $this->methods = [];
            $this->server_name = Converter::functionNameSafe($server_name);
        }

    }
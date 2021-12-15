<?php

    namespace KimchiAPI;

    // Define server information for response headers
    use KimchiAPI\Exceptions\MethodAlreadyRegisteredException;
    use KimchiAPI\Exceptions\MissingComponentsException;
    use KimchiAPI\Interfaces\MethodInterface;
    use KimchiAPI\Utilities\Converter;
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
         * @var array
         */
        private array $methods;

        private string $server_name;

        /**
         * Server constructor.
         * @param string $server_name
         */
        public function __construct(string $server_name)
        {
            $this->methods = [];
            $this->server_name = Converter::functionNameSafe($server_name);
        }

        /**
         * @param MethodInterface $method
         * @throws MethodAlreadyRegisteredException
         */
        public function registerMethod(MethodInterface $method)
        {
            if(isset($this->methods[$method->getVersion() . ':' . $method->getMethod()]))
                throw new MethodAlreadyRegisteredException('The method ' . $method->getMethod() . ' (' . $method->getVersion() . ') is already registered');

            $this->methods[$method->getVersion() . ':' . $method->getMethod()] = $method;
            $this->reorderMethods();
        }

        /**
         * Reorders the methods into alphabetical order
         */
        private function reorderMethods()
        {
            $method_reordered = array_keys($this->methods);
            sort($method_reordered);
            $methods_clean = [];

            foreach($method_reordered as $method_name)
            {
                if(is_int($method_name) == false)
                    $methods_clean[$method_name] = $this->methods[$method_name];
            }

            $this->methods = $methods_clean;
        }

    }
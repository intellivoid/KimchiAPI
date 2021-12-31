<?php

    namespace KimchiAPI;

    // Define server information for response headers
    use Exception;
    use KimchiAPI\Abstracts\Method;
    use KimchiAPI\Exceptions\IOException;
    use KimchiAPI\Exceptions\MethodAlreadyRegisteredException;
    use KimchiAPI\Exceptions\MethodNotFoundException;
    use KimchiAPI\Exceptions\MissingComponentsException;
    use KimchiAPI\Interfaces\MethodInterface;
    use KimchiAPI\Objects\Request;
    use KimchiAPI\Objects\Response;
    use KimchiAPI\Utilities\Converter;
    use RecursiveDirectoryIterator;
    use RecursiveIteratorIterator;
    use RegexIterator;
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
         * An array of declared methods
         *
         * @var string[]
         */
        private $methods_path;

        /**
         * Server constructor.
         */
        public function __construct()
        {
            $this->methods_path = [];
        }

        /**
         * Add a single custom commands path
         *
         * @param string $path   Custom methods' path to add
         * @param string $version The version of the methods to add
         * @param bool   $before If the path should be prepended or appended to the list
         */
        public function addMethodsPath(string $path, string $version, bool $before=true)
        {
            if (!is_dir($path))
            {
                new IOException('Methods path "' . $path . '" does not exist.');
            }

            $this->methods_path[$version] = $path;
        }

        /**
         * Sanitize Method
         *
         * @param string $command
         * @return string
         */
        protected function sanitizeMethod(string $command): string
        {
            return str_replace(' ', '', $this->ucWordsUnicode(str_replace('_', ' ', $command)));
        }

        /**
         * Replace function `ucwords` for UTF-8 characters in the class definition and commands
         *
         * @param string $str
         * @param string $encoding (default = 'UTF-8')
         *
         * @return string
         */
        protected function ucWordsUnicode(string $str, string $encoding = 'UTF-8'): string
        {
            return mb_convert_case($str, MB_CASE_TITLE, $encoding);
        }

        /**
         * Replace function `ucfirst` for UTF-8 characters in the class definition and commands
         *
         * @param string $str
         * @param string $encoding (default = 'UTF-8')
         *
         * @return string
         */
        protected function ucFirstUnicode(string $str, string $encoding = 'UTF-8'): string
        {
            return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding)
                . mb_strtolower(mb_substr($str, 1, mb_strlen($str), $encoding), $encoding);
        }

        public function getCommandsList(): array
        {
            $commands = [];

            foreach ($this->methods_path as $version)
            {
                foreach($this->methods_path[$version] as $path)
                {
                    try {
                        //Get all "*Command.php" files
                        $files = new RegexIterator(
                            new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($path)
                            ),
                            '/^.+Method.php$/'
                        );

                        foreach ($files as $file) {
                            //Remove "Method.php" from filename
                            $command      = $this->sanitizeMethod(substr($file->getFilename(), 0, -11));
                            $command_name = mb_strtolower($command);

                            if (array_key_exists($command_name, $commands)) {
                                continue;
                            }

                            require_once $file->getPathname();

                            $command_obj = $this->getMethoddObject($command, $file->getPathname());
                            if ($command_obj instanceof Method)
                            {
                                $commands[$command_name] = $command_obj;
                            }
                        }
                    } catch (Exception $e) {
                        throw new IOException('Error getting commands from path: ' . $path, $e);
                    }
                }

            }

            return $commands;
        }


        /**
         * Get an object instance of the passed command
         *
         * @param string $command
         * @param string $filepath
         *
         * @return Method|null
         */
        public function getMethoddObject(string $command, string $filepath = ''): ?Method
        {
            if (isset($this->commands_objects[$command])) {
                return $this->commands_objects[$command];
            }

            foreach ($which as $auth) {
                $command_class = $this->getCommandClassName($auth, $command, $filepath);

                if ($command_class) {
                    $command_obj = new $command_class($this, $this->update);

                    if ($auth === Command::AUTH_SYSTEM && $command_obj instanceof SystemCommand) {
                        return $command_obj;
                    }
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
            $auth    = $this->ucFirstUnicode($auth);

            // First, check for directly assigned command class.
            if ($command_class = $this->command_classes[$auth][$command] ?? null) {
                return $command_class;
            }

            // Start with default namespace.
            $command_namespace = __NAMESPACE__ . '\\Commands\\' . $auth . 'Commands';

            // Check if we can get the namespace from the file (if passed).
            if ($filepath && !($command_namespace = $this->getFileNamespace($filepath))) {
                return null;
            }

            $command_class = $command_namespace . '\\' . $this->ucFirstUnicode($command) . 'Command';

            if (class_exists($command_class)) {
                return $command_class;
            }

            return null;
        }

        /**
         * Get namespace from php file by src path
         *
         * @param string $src (absolute path to file)
         * @return string|null
         */
        protected function getFileNamespace(string $src): ?string
        {
            $content = file_get_contents($src);
            if (preg_match('#^\s*namespace\s+(.+?);#m', $content, $m)) {
                return $m[1];
            }

            return null;
        }

        /**
         * Get classname of predefined commands
         *
         * @see command_classes
         * @param string $auth     Auth of command
         * @param string $command  Command name
         * @param string $filepath Path to the command file
         * @return string|null
         */
        public function getMethodClassName(string $auth, string $command, string $filepath = ''): ?string
        {
            $command = mb_strtolower($command);
            $auth    = $this->ucFirstUnicode($auth);

            // First, check for directly assigned command class.
            if ($command_class = $this->command_classes[$auth][$command] ?? null)
            {
                return $command_class;
            }

            // Start with default namespace.
            $command_namespace = __NAMESPACE__ . '\\Commands\\' . $auth . 'Commands';

            // Check if we can get the namespace from the file (if passed).
            if ($filepath && !($command_namespace = $this->getFileNamespace($filepath)))
            {
                return null;
            }

            $command_class = $command_namespace . '\\' . $this->ucFirstUnicode($command) . 'Command';

            if (class_exists($command_class))
            {
                return $command_class;
            }

            return null;
        }


    }
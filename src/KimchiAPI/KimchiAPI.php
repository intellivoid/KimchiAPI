<?php

    namespace KimchiAPI;

    // Define server information for response headers
    use KimchiAPI\Abstracts\Command;
    use KimchiAPI\Exceptions\IOException;
    use KimchiAPI\Exceptions\MissingComponentsException;
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
        private $commands_paths;

        /**
         * @var array
         */
        private $command_classes;

        /**
         * Server constructor.
         */
        public function __construct()
        {
            $this->commands_paths = [];
            $this->command_classes = [];
        }

        /**
         * Return the list of commands paths
         *
         * @return array
         */
        public function getCommandsPaths(): array
        {
            return $this->commands_paths;
        }

        /**
         * Return the list of command classes
         *
         * @return array
         */
        public function getCommandClasses(): array
        {
            return $this->command_classes;
        }


        /**
         * Add a single custom commands path
         *
         * @param string $path Custom commands path to add
         * @param bool $before If the path should be prepended or appended to the list
         * @throws IOException
         */
        public function addCommandsPath(string $path, bool $before=true)
        {
            if (!is_dir($path))
            {
                throw new IOException('Commands path "' . $path . '" does not exist.');
            }
            elseif (!in_array($path, $this->commands_paths, true))
            {
                if ($before)
                {
                    array_unshift($this->commands_paths, $path);
                }
                else
                {
                    $this->commands_paths[] = $path;
                }
            }
        }

        /**
         * Add multiple custom commands paths
         *
         * @param array $paths Custom commands paths to add
         * @param bool $before If the paths should be prepended or appended to the list
         * @throws IOException
         */
        public function addCommandsPaths(array $paths, bool $before=true)
        {
            foreach ($paths as $path)
            {
                $this->addCommandsPath($path, $before);
            }
        }


        /**
         * Get an object instance of the passed command
         *
         * @param string $command
         * @param string $filepath
         *
         * @return Command|null
         */
        public function getCommandObject(string $command, string $filepath = ''): ?Command
        {
            if (isset($this->commands_objects[$command]))
            {
                return $this->commands_objects[$command];
            }

            $which = [Command::AUTH_SYSTEM];
            $which[] = Command::AUTH_USER;

            foreach ($which as $auth)
            {
                $command_class = $this->getCommandClassName($auth, $command, $filepath);

                if ($command_class)
                {
                    return new $command_class();
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
            $auth = Converter::ucFirstUnicode($auth);

            // First, check for directly assigned command class.
            if ($command_class = $this->command_classes[$auth][$command] ?? null)
            {
                return $command_class;
            }

            // Start with default namespace.
            $command_namespace = __NAMESPACE__ . '\\Commands\\' . $auth . 'Commands';

            // Check if we can get the namespace from the file (if passed).
            if ($filepath && !($command_namespace = Converter::getFileNamespace($filepath)))
            {
                return null;
            }

            $command_class = $command_namespace . '\\' . Converter::ucFirstUnicode($command) . 'Command';

            if (class_exists($command_class))
            {
                return $command_class;
            }

            return null;
        }
    }
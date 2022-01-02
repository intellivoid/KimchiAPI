<?php

    namespace KimchiAPI\Objects\Configuration;

    use KimchiAPI\Abstracts\RequestMethod;

    class MethodConfiguration
    {
        /**
         * An array of accepted request methods that this method accepts
         *
         * @var RequestMethod[]
         */
        public $Methods;

        /**
         * The HTTP route path for this request method
         *
         * @var string
         */
        public $Path;

        /**
         * The class name to initialize
         *
         * @var string
         */
        public $Class;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'methods' => $this->Methods,
                'path' => $this->Path,
                'class' => $this->Class
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return MethodConfiguration
         */
        public static function fromArray(array $data): MethodConfiguration
        {
            $MethodConfigurationObject = new MethodConfiguration();

            if(isset($data['methods']))
                $MethodConfigurationObject->Methods = $data['methods'];

            if(isset($data['path']))
                $MethodConfigurationObject->Path = $data['path'];

            if(isset($data['class']))
                $MethodConfigurationObject->Class = $data['class'];

            return $MethodConfigurationObject;
        }
    }
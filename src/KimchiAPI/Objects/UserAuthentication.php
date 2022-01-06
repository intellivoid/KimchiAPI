<?php

    namespace KimchiAPI\Objects;

    class UserAuthentication
    {
        /**
         * Returns a username representation of the object
         *
         * @var string
         */
        public $Username;

        /**
         * The password representation of the object
         *
         * @var string
         */
        public $Password;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'username' => $this->Username,
                'password' => $this->Password
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return UserAuthentication
         */
        public static function fromArray(array $data): UserAuthentication
        {
            $UserAuthenticationObject = new UserAuthentication();

            if(isset($data['username']))
                $UserAuthenticationObject->Username = $data['username'];

            if(isset($data['password']))
                $UserAuthenticationObject->Password = $data['password'];

            return $UserAuthenticationObject;
        }
    }
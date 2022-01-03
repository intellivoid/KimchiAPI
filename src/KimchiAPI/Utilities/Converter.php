<?php

    namespace KimchiAPI\Utilities;

    use Exception;
    use KimchiAPI\Abstracts\ResponseType;
    use KimchiAPI\Exceptions\UnsupportedResponseTypeExceptions;
    use Symfony\Component\Yaml\Yaml;
    use ZiProto\ZiProto;

    class Converter
    {
        /**
         * Converts an exception to an array representation
         *
         * @param Exception $e
         * @return array
         */
        public static function exceptionToArray(Exception $e): array
        {
            $return_results = [
                'file_path' => $e->getFile(),
                'line' => $e->getLine(),
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'trace' => $e->getTrace()
            ];

            if($e->getPrevious() !== null)
            {
                /** @noinspection PhpParamsInspection */
                $return_results['previous'] = self::exceptionToArray($e->getPrevious());
            }

            return $return_results;
        }

        /**
         * Replace function `ucwords` for UTF-8 characters in the class definition and commands
         *
         * @param string $str
         * @param string $encoding (default = 'UTF-8')
         *
         * @return string
         */
        public static function ucWordsUnicode(string $str, string $encoding = 'UTF-8'): string
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
        public static function ucFirstUnicode(string $str, string $encoding = 'UTF-8'): string
        {
            return mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding)
                . mb_strtolower(mb_substr($str, 1, mb_strlen($str), $encoding), $encoding);
        }

        /**
         * Get namespace from php file by src path
         *
         * @param string $src (absolute path to file)
         * @return string|null
         */
        public static function getFileNamespace(string $src): ?string
        {
            $content = file_get_contents($src);
            if (preg_match('#^\s*namespace\s+(.+?);#m', $content, $m))
            {
                return $m[1];
            }

            return null;
        }

        /**
         * Serializes the response
         *
         * @param array $data
         * @param string $response_type
         * @return string
         * @throws UnsupportedResponseTypeExceptions
         */
        public static function serializeResponse(array $data, string $response_type): string
        {
            if($response_type == ResponseType::Automatic)
                $response_type = ResponseType::Json;

            switch($response_type)
            {
                case ResponseType::Json:
                    return json_encode($data, JSON_UNESCAPED_SLASHES);

                case ResponseType::ZiProto:
                case ResponseType::Msgpack:
                    return ZiProto::encode($data);

                case ResponseType::Yaml:
                    return Yaml::dump($data);

                default:
                    throw new UnsupportedResponseTypeExceptions('The response type \'' . $response_type . '\' is not supported by KimchiAPI');
            }
        }
    }
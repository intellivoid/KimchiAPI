<?php

    namespace KimchiAPI\Utilities;

    class Converter
    {
        /**
         * Converts the string to a function safe name
         *
         * @param string $input
         * @return string
         */
        public static function functionNameSafe(string $input): string
        {
            $out = preg_replace("/(?>[^\w\.])+/", "_", $input);
            // Replace any underscores at start or end of the string.
            if ($out[0] == "_")
            {
                $out = substr($out, 1);
            }
            if ($out[-1] == "_")
            {
                $out = substr($out, 0, -1);
            }

            return $out;
        }

        /**
         * Truncates a long string
         *
         * @param string $input
         * @param int $length
         * @return string
         */
        public static function truncateString(string $input, int $length): string
        {
            return (strlen($input) > $length) ? substr($input,0, $length).'...' : $input;
        }

        /**
         * Sanitize Method
         *
         * @param string $command
         * @return string
         */
        public static function sanitizeMethod(string $command): string
        {
            return str_replace(' ', '', self::ucWordsUnicode(str_replace('_', ' ', $command)));
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
    }
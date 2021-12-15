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
    }
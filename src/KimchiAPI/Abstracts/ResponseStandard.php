<?php

    namespace KimchiAPI\Abstracts;

    abstract class ResponseStandard
    {
        /**
         * Returns the classic Intellivoid API standard structure
         */
        const IntellivoidAPI = 'INTELLIVOID_API';

        /**
         * Returns the re-defined Kimchi API standard
         */
        const KimchiAPI = 'KIMCHI_API';

        /**
         * Returns the jsonapi.org standard
         */
        const JsonApiOrg = 'JSONAPI.ORG';

        /**
         * Returns the Google API response standard
         */
        const GoogleAPI = 'GOOGLE_API';
    }
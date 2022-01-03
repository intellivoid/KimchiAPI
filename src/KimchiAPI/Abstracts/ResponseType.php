<?php

    namespace KimchiAPI\Abstracts;

    abstract class ResponseType
    {
        const Automatic = 'automatic';

        const Json = 'application/json';

        const Msgpack = 'application/x-msgpack';

        const ZiProto = 'application/ziproto';

        const XML = 'text/xml';

        const Yaml = 'text/x-yaml';
    }
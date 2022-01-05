<?php

    namespace KimchiAPI\Classes;

    use KimchiAPI\Exceptions\RouterException;
    use Traversable;

    class Router
    {
        /**
         * @var array Array of all routes (incl. named routes).
         */
        protected $routes = array();

        /**
         * @var array Array of all named routes.
         */
        protected $namedRoutes = array();

        /**
         * @var string Can be used to ignore leading part of the Request URL (if main file lives in subdirectory of host)
         */
        protected $basePath = '';

        /**
         * @var array Array of default match types (regex helpers)
         */
        protected $matchTypes = array(
            'i'  => '[0-9]++',
            'a'  => '[0-9A-Za-z]++',
            'h'  => '[0-9A-Fa-f]++',
            '*'  => '.+?',
            '**' => '.++',
            ''   => '[^/\.]++'
        );

        /**
         * Create router in one call from config.
         *
         * @param array $routes
         * @param string $basePath
         * @param array $matchTypes
         * @throws RouterException
         */
        public function __construct(array $routes=[], string $basePath = '', array $matchTypes=[])
        {
            $this->addRoutes($routes);
            $this->setBasePath($basePath);
            $this->addMatchTypes($matchTypes);
        }

        /**
         * Retrieves all routes.
         * Useful if you want to process or display routes.
         * @return array All routes.
         */
        public function getRoutes(): array
        {
            return $this->routes;
        }

        /**
         * Add multiple routes at once from array in the following format:
         *
         *   $routes = array(
         *      array($method, $route, $target, $name)
         *   );
         *
         * @param array $routes
         * @return void
         * @throws RouterException
         */
        public function addRoutes(array $routes)
        {
            /** @noinspection PhpConditionAlreadyCheckedInspection */
            if(!is_array($routes) && !$routes instanceof Traversable)
            {
                throw new RouterException('Routes should be an array or an instance of Traversable');
            }

            foreach($routes as $route)
            {
                call_user_func_array(array($this, 'map'), $route);
            }
        }

        /**
         * Set the base path.
         * Useful if you are running your application from a subdirectory.
         * @param $basePath
         */
        public function setBasePath($basePath)
        {
            $this->basePath = $basePath;
        }

        /**
         * Add named match types. It uses array_merge so keys can be overwritten.
         *
         * @param array $matchTypes The key is the name and the value is the regex.
         */
        public function addMatchTypes(array $matchTypes)
        {
            $this->matchTypes = array_merge($this->matchTypes, $matchTypes);
        }

        /**
         * Map a route to a target
         *
         * @param string $method One of 5 HTTP Methods, or a pipe-separated list of multiple HTTP Methods (GET|POST|PATCH|PUT|DELETE)
         * @param string $route The route regex, custom regex must start with an @. You can use multiple pre-set regex filters, like [i:id]
         * @param mixed $target The target where this route should point to. Can be anything.
         * @param string|null $name Optional name of this route. Supply if you want to reverse route this url in your application.
         * @throws RouterException
         * @noinspection PhpMissingParamTypeInspection
         * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
         * @noinspection RedundantSuppression
         */
        public function map(string $method, string $route, $target, string $name=null)
        {
            $route = KIMCHI_API_ROOT_PATH . $route;
            $this->routes[] = array($method, $route, $target, $name);

            if($name)
            {
                if(isset($this->namedRoutes[$name]))
                {
                    throw new RouterException("Can not redeclare route '{$name}'");
                }

                $this->namedRoutes[$name] = $route;
            }
        }

        /**
         * Reversed routing
         *
         * Generate the URL for a named route. Replace regexes with supplied parameters
         *
         * @param string $routeName The name of the route.
         * @param array $params
         * @return string The URL of the route with named parameters in place.
         * @throws RouterException
         * @noinspection PhpUnnecessaryCurlyVarSyntaxInspection
         */
        public function generate(string $routeName, array $params=[]): string
        {

            // Check if named route exists
            if(!isset($this->namedRoutes[$routeName]))
            {
                throw new RouterException("Route '{$routeName}' does not exist.");
            }

            // Replace named parameters
            $route = $this->namedRoutes[$routeName];

            // prepend base path to route url again
            $url = $this->basePath . $route;

            /** @noinspection RegExpRedundantEscape */
            if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER))
            {

                foreach($matches as $index => $match)
                {
                    /** @noinspection PhpUnusedLocalVariableInspection */
                    list($block, $pre, $type, $param, $optional) = $match;

                    if ($pre)
                    {
                        $block = substr($block, 1);
                    }

                    if(isset($params[$param]))
                    {
                        // Part is found, replace for param value
                        $url = str_replace($block, $params[$param], $url);
                    }
                    elseif ($optional && $index !== 0)
                    {
                        // Only strip proceeding slash if it's not at the base
                        $url = str_replace($pre . $block, '', $url);
                    }
                    else
                    {
                        // Strip match block
                        $url = str_replace($block, '', $url);
                    }
                }

            }

            return $url;
        }

        /**
         * Match a given Request Url against stored routes
         * @param string|null $requestUrl
         * @param string|null $requestMethod
         * @return array|boolean Array with route information on success, false on failure (no match).
         * @noinspection PhpIssetCanBeReplacedWithCoalesceInspection
         */
        public function match(?string $requestUrl=null, string $requestMethod = null)
        {
            $params = array();
            /** @noinspection PhpUnusedLocalVariableInspection */
            $match = false;

            // set Request Url if it isn't passed as parameter
            if($requestUrl === null)
            {
                $requestUrl = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
            }

            // strip base path from request url
            $requestUrl = substr($requestUrl, strlen($this->basePath));

            // Strip query string (?a=b) from Request Url
            /** @noinspection SpellCheckingInspection */
            if (($strpos = strpos($requestUrl, '?')) !== false)
            {
                $requestUrl = substr($requestUrl, 0, $strpos);
            }

            // set Request Method if it isn't passed as a parameter
            if($requestMethod === null)
            {
                $requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            }

            foreach($this->routes as $handler)
            {

                list($methods, $route, $target, $name) = $handler;
                $method_match = (stripos($methods, $requestMethod) !== false);

                // Method did not match, continue to next route.
                if (!$method_match) continue;

                if ($route === '*')
                {
                    // * wildcard (matches all)
                    $match = true;
                }
                elseif (isset($route[0]) && $route[0] === '@')
                {
                    // @ regex delimiter
                    $pattern = '`' . substr($route, 1) . '`u';
                    $match = preg_match($pattern, $requestUrl, $params) === 1;
                }
                elseif (($position = strpos($route, '[')) === false)
                {
                    // No params in url, do string comparison
                    $match = strcmp($requestUrl, $route) === 0;
                }
                else
                {
                    // Compare longest non-param string with url
                    if (strncmp($requestUrl, $route, $position) !== 0)
                    {
                        continue;
                    }
                    $regex = $this->compileRoute($route);
                    $match = preg_match($regex, $requestUrl, $params) === 1;
                }

                if ($match)
                {

                    if ($params)
                    {
                        foreach($params as $key => $value)
                        {
                            if(is_numeric($key)) unset($params[$key]);
                        }
                    }

                    Request::setDefinedDynamicParameters($params);

                    return array(
                        'target' => $target,
                        'params' => $params,
                        'name' => $name
                    );
                }
            }
            return false;
        }

        /**
         * Compile the regex for a given route (EXPENSIVE)
         * @param $route
         * @return string
         */
        protected function compileRoute($route): string
        {
            /** @noinspection RegExpRedundantEscape */
            if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER))
            {

                $matchTypes = $this->matchTypes;
                foreach($matches as $match)
                {
                    list($block, $pre, $type, $param, $optional) = $match;

                    if (isset($matchTypes[$type]))
                    {
                        $type = $matchTypes[$type];
                    }

                    if ($pre === '.')
                    {
                        $pre = '\.';
                    }

                    $optional = $optional !== '' ? '?' : null;

                    //Older versions of PCRE require the 'P' in (?P<named>)
                    $pattern = '(?:'
                        . ($pre !== '' ? $pre : null)
                        . '('
                        . ($param !== '' ? "?P<$param>" : null)
                        . $type
                        . ')'
                        . $optional
                        . ')'
                        . $optional;

                    $route = str_replace($block, $pattern, $route);
                }

            }
            return "`^(?J)$route$`u";
        }

        /**
         * @return string
         */
        public function getBasePath(): string
        {
            return $this->basePath;
        }

    }
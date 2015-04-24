<?hh // strict

namespace FastRoute\Dispatcher;

use FastRoute\Dispatcher;

abstract class RegexBasedAbstract implements Dispatcher {
    protected array<string, mixed> $staticRouteMap;
    protected array<string, mixed> $variableRouteData;

    protected abstract function dispatchVariableRoute(array<array<string, mixed>> $routeData, string $uri): array<mixed>;

    public function dispatch(string $httpMethod, string $uri): array<mixed> {
        if (array_key_exists($uri, $this->staticRouteMap)) {
            return $this->dispatchStaticRoute($httpMethod, $uri);
        }

        $varRouteData = $this->variableRouteData;
        if (array_key_exists($httpMethod, $varRouteData)) {
            $routeData = $varRouteData[$httpMethod];
            invariant(is_array($routeData), 'routeData item must be an array');
            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        } else if ($httpMethod === 'HEAD' && array_key_exists('GET', $varRouteData)) {
            $routeData = $varRouteData['GET'];
            invariant(is_array($routeData), 'routeData item must be an array');
            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) {
                return $result;
            }
        }

        // Find allowed methods for this URI by matching against all other
        // HTTP methods as well
        $allowedMethods = [];
        foreach ($varRouteData as $method => $routeData) {
            if ($method === $httpMethod) {
                continue;
            }

            invariant(is_array($routeData), 'routeData item must be an array');
            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) {
                $allowedMethods[] = $method;
            }
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods) {
            return [self::METHOD_NOT_ALLOWED, $allowedMethods];
        } else {
            return [self::NOT_FOUND];
        }
    }

    protected function dispatchStaticRoute(string $httpMethod, string $uri): array<mixed> {
        $routes = $this->staticRouteMap[$uri];

        invariant(is_array($routes), 'staticRouteMap item must be an array');
        if (array_key_exists($httpMethod, $routes)) {
            return [self::FOUND, $routes[$httpMethod], []];
        } elseif ($httpMethod === 'HEAD' && array_key_exists('GET', $routes)) {
            return [self::FOUND, $routes['GET'], []];
        } else {
            return [self::METHOD_NOT_ALLOWED, array_keys($routes)];
        }
    }
}

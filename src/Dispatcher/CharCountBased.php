<?hh // strict

namespace FastRoute\Dispatcher;

class CharCountBased extends RegexBasedAbstract {
    public function __construct((array<string, array<string, mixed>>, array<string, array<array<string, mixed>>>) $data) {
        list($this->staticRouteMap, $this->variableRouteData) = $data;
    }

    protected function dispatchVariableRoute(array<array<string, mixed>> $routeData, string $uri): array<mixed> {
        foreach ($routeData as $data) {
            $suffix = $data['suffix'];
            invariant(is_string($suffix), 'routeData item key suffix must be a string');
            $matches = [];
            if (!preg_match($data['regex'], $uri . $suffix, $matches)) {
                continue;
            }

            $routeMap = $data['routeMap'];
            invariant(is_array($routeMap), 'routeData item key routeMap must be an array');
            list($handler, $varNames) = $routeMap[end($matches)];

            $vars = [];
            $i = 0;
            foreach ($varNames as $varName) {
                $vars[$varName] = $matches[++$i];
            }
            return [self::FOUND, $handler, $vars];
        }

        return [self::NOT_FOUND];
    }
}

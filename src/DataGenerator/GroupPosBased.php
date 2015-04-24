<?hh // strict

namespace FastRoute\DataGenerator;
use FastRoute\Route;

class GroupPosBased extends RegexBasedAbstract {
    protected function getApproxChunkSize(): int {
        return 10;
    }

    public function processChunk(array<string, Route> $regexToRoutesMap): array<string, mixed> {
        $routeMap = [];
        $regexes = [];
        $offset = 1;
        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex;
            $routeMap[$offset] = [$route->handler, $route->variables];

            $offset += count($route->variables);
        }

        $regex = '~^(?:' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}


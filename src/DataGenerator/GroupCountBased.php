<?hh // strict

namespace FastRoute\DataGenerator;
use FastRoute\Route;

class GroupCountBased extends RegexBasedAbstract {
    protected function getApproxChunkSize(): int {
        return 10;
    }

    public function processChunk(array<string, Route> $regexToRoutesMap): array<string, mixed> {
        $routeMap = [];
        $regexes = [];
        $numGroups = 0;
        foreach ($regexToRoutesMap as $regex => $route) {
            $numVariables = count($route->variables);
            $numGroups = max($numGroups, $numVariables);

            $regexes[] = $regex . str_repeat('()', $numGroups - $numVariables);
            $routeMap[$numGroups + 1] = [$route->handler, $route->variables];

            ++$numGroups;
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}


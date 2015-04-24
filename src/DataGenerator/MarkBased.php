<?hh // strict

namespace FastRoute\DataGenerator;
use FastRoute\Route;

class MarkBased extends RegexBasedAbstract {
    protected function getApproxChunkSize(): int {
        return 30;
    }

    public function processChunk(array<string, Route> $regexToRoutesMap): array<string, mixed> {
        $routeMap = [];
        $regexes = [];
        $markName = 'a';
        foreach ($regexToRoutesMap as $regex => $route) {
            $regexes[] = $regex . '(*MARK:' . $markName . ')';
            $routeMap[$markName] = [$route->handler, $route->variables];

            $markName = chr(ord($markName)+1);
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'routeMap' => $routeMap];
    }
}


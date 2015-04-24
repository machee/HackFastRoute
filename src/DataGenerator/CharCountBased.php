<?hh // strict

namespace FastRoute\DataGenerator;
use FastRoute\Route;

class CharCountBased extends RegexBasedAbstract {
    protected function getApproxChunkSize(): int {
        return 30;
    }

    public function processChunk(array<string, Route> $regexToRoutesMap): array<string, mixed> {
        $routeMap = [];
        $regexes = [];

        $suffixLen = 0;
        $suffix = '';
        $count = count($regexToRoutesMap);
        foreach ($regexToRoutesMap as $regex => $route) {
            $suffixLen++;
            $suffix .= "\t";

            $regexes[] = '(?:' . $regex . '/(\t{' . $suffixLen . '})\t{' . ($count - $suffixLen) . '})';
            $routeMap[$suffix] = [$route->handler, $route->variables];
        }

        $regex = '~^(?|' . implode('|', $regexes) . ')$~';
        return ['regex' => $regex, 'suffix' => '/' . $suffix, 'routeMap' => $routeMap];
    }
}

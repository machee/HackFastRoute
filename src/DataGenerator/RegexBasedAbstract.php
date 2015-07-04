<?hh // strict

namespace FastRoute\DataGenerator;

use FastRoute\DataGenerator;
use FastRoute\BadRouteException;
use FastRoute\Route;

abstract class RegexBasedAbstract implements DataGenerator {
    protected array<string, array<string, mixed>> $staticRoutes = [];
    protected array<string, array<string, Route>> $methodToRegexToRoutesMap = [];

    protected abstract function getApproxChunkSize(): int;
    public abstract function processChunk(array<string, Route> $regexToRoutesMap): array<string, mixed>;

    public function addRoute(string $httpMethod, array<mixed> $routeData, mixed $handler): void {
        if ($this->isStaticRoute($routeData)) {
            $this->addStaticRoute($httpMethod, $routeData, $handler);
        } else {
            $this->addVariableRoute($httpMethod, $routeData, $handler);
        }
    }

    public function getData(): mixed {
        if (!count($this->methodToRegexToRoutesMap)) {
            return [$this->staticRoutes, []];
        }

        return [$this->staticRoutes, $this->generateVariableRouteData()];
    }

    private function generateVariableRouteData(): array<mixed> {
        $data = [];
        foreach ($this->methodToRegexToRoutesMap as $method => $regexToRoutesMap) {
            $chunkSize = $this->computeChunkSize(count($regexToRoutesMap));
            $chunks = array_chunk($regexToRoutesMap, $chunkSize, true);
            $data[$method] = array_map(inst_meth($this, 'processChunk'), $chunks);
        }
        return $data;
    }

    private function computeChunkSize(int $count): int {
        $numParts = max(1, round($count / $this->getApproxChunkSize()));
        return (int) ceil($count / $numParts);
    }

    private function isStaticRoute(array<mixed> $routeData): bool {
        return count($routeData) == 1 && is_string($routeData[0]);
    }

    private function addStaticRoute(string $httpMethod, array<mixed> $routeData, mixed $handler): void {
        $routeStr = $routeData[0];
        invariant(is_string($routeStr), 'routeData must start with string');

        if (array_key_exists($httpMethod, $this->staticRoutes) &&
                array_key_exists($routeStr, $this->staticRoutes[$httpMethod])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $routeStr, $httpMethod
            ));
        }

        if (array_key_exists($httpMethod, $this->methodToRegexToRoutesMap)) {
            foreach ($this->methodToRegexToRoutesMap[$httpMethod] as $route) {
                if ($route->matches($routeStr)) {
                    throw new BadRouteException(sprintf(
                        'Static route "%s" is shadowed by previously defined variable route "%s" for method "%s"',
                        $routeStr, $route->regex, $httpMethod
                    ));
                }
            }
        }

        $this->staticRoutes[$httpMethod][$routeStr] = $handler;
    }

    private function addVariableRoute(string $httpMethod, array<mixed> $routeData, mixed $handler): void {
        list($regex, $variables) = $this->buildRegexForRoute($routeData);

        if (array_key_exists($httpMethod, $this->methodToRegexToRoutesMap) && array_key_exists($regex, $this->methodToRegexToRoutesMap[$httpMethod])) {
            throw new BadRouteException(sprintf(
                'Cannot register two routes matching "%s" for method "%s"',
                $regex, $httpMethod
            ));
        }

        $this->methodToRegexToRoutesMap[$httpMethod][$regex] = new Route(
            $httpMethod, $handler, $regex, $variables
        );
    }

    private function buildRegexForRoute(array<mixed> $routeData): (string, array<string, string>) {
        $regex = '';
        $variables = [];
        foreach ($routeData as $part) {
            if (is_string($part)) {
                $regex .= preg_quote($part, '~');
                continue;
            }

            invariant(is_array($part), 'routeData parts must be string or array');
            list($varName, $regexPart) = $part;

            if (array_key_exists($varName, $variables)) {
                throw new BadRouteException(sprintf(
                    'Cannot use the same placeholder "%s" twice', $varName
                ));
            }

            if ($this->regexHasCapturingGroups($regexPart)) {
                throw new BadRouteException(sprintf(
                    'Regex "%s" for parameter "%s" contains a capturing group',
                    $regexPart, $varName
                ));
            }

            $variables[$varName] = $varName;
            $regex .= '(' . $regexPart . ')';
        }

        return tuple($regex, $variables);
    }

    private function regexHasCapturingGroups(string $regex): bool {
        if (false === strpos($regex, '(')) {
            // Needs to have at least a ( to contain a capturing group
            return false;
        }

        // Semi-accurate detection for capturing groups
        return preg_match(
            '~
                (?:
                    \(\?\(
                  | \[ [^\]\\\\]* (?: \\\\ . [^\]\\\\]* )* \]
                  | \\\\ .
                ) (*SKIP)(*FAIL) |
                \(
                (?!
                    \? (?! <(?![!=]) | P< | \' )
                  | \*
                )
            ~x',
            $regex
        ) === 1;
    }
}

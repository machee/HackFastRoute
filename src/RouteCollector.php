<?hh // strict

namespace FastRoute;

class RouteCollector {
    private RouteParser $routeParser;
    private DataGenerator $dataGenerator;

    /**
     * Constructs a route collector.
     *
     * @param RouteParser   $routeParser
     * @param DataGenerator $dataGenerator
     */
    public function __construct(RouteParser $routeParser, DataGenerator $dataGenerator): void {
        $this->routeParser = $routeParser;
        $this->dataGenerator = $dataGenerator;
    }

    /**
     * Adds a route to the collection.
     *
     * The syntax used in the $route string depends on the used route parser.
     *
     * @param string|string[] $httpMethod
     * @param string $route
     * @param mixed  $handler
     */
    public function addRoute(mixed $httpMethod, string $route, mixed $handler): void {
        $routeData = $this->routeParser->parse($route);
        if (!is_array($httpMethod)) {
            $httpMethod = [$httpMethod];
        }
        foreach ($httpMethod as $method) {
            invariant(is_string($method), 'Methods must be string');
            $this->dataGenerator->addRoute($method, $routeData, $handler);
        }
    }

    /**
     * Returns the collected route data, as provided by the data generator.
     *
     * @return array
     */
    public function getData(): mixed {
        return $this->dataGenerator->getData();
    }
}

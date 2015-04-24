<?hh // strict

namespace FastRoute;

class Route {
    public string $httpMethod;
    public string $regex;
    public array<string, string> $variables;
    public mixed $handler;

    /**
     * Constructs a route (value object).
     *
     * @param string $httpMethod
     * @param mixed  $handler
     * @param string $regex
     * @param array  $variables
     */
    public function __construct(
        string $httpMethod,
        mixed $handler,
        string $regex,
        array<string, string> $variables
    ) {
        $this->httpMethod = $httpMethod;
        $this->handler = $handler;
        $this->regex = $regex;
        $this->variables = $variables;
    }

    /**
     * Tests whether this route matches the given string.
     *
     * @param string $str
     *
     * @return bool
     */
    public function matches(string $str): bool {
        $regex = '~^' . $this->regex . '$~';
        return (bool) preg_match($regex, $str);
    }
}


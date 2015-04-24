<?hh

namespace FastRoute\Dispatcher;

class CharCountBasedTest extends DispatcherTest {
    protected function getDispatcherClass(): string {
        return 'FastRoute\\Dispatcher\\CharCountBased';
    }

    protected function getDataGeneratorClass(): string {
        return 'FastRoute\\DataGenerator\\CharCountBased';
    }
}

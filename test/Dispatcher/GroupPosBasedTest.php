<?hh

namespace FastRoute\Dispatcher;

class GroupPosBasedTest extends DispatcherTest {
    protected function getDispatcherClass(): string {
        return 'FastRoute\\Dispatcher\\GroupPosBased';
    }

    protected function getDataGeneratorClass(): string {
        return 'FastRoute\\DataGenerator\\GroupPosBased';
    }
}

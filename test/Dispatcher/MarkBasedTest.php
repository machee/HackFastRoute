<?hh

namespace FastRoute\Dispatcher;

class MarkBasedTest extends DispatcherTest {
    public function setUp(): void {
        $matches = [];
        preg_match('/(*MARK:A)a/', 'a', $matches);
        if (!array_key_exists('MARK', $matches)) {
            $this->markTestSkipped('PHP 5.6 required for MARK support');
        }
    }

    protected function getDispatcherClass(): string {
        return 'FastRoute\\Dispatcher\\MarkBased';
    }

    protected function getDataGeneratorClass(): string {
        return 'FastRoute\\DataGenerator\\MarkBased';
    }
}

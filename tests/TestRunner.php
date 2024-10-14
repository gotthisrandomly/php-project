<?php
class TestRunner {
    private $tests = [];

    public function addTest($testClass) {
        $this->tests[] = $testClass;
    }

    public function run() {
        $totalTests = 0;
        $passedTests = 0;

        foreach ($this->tests as $testClass) {
            $test = new $testClass();
            $methods = get_class_methods($test);

            foreach ($methods as $method) {
                if (strpos($method, 'test') === 0) {
                    $totalTests++;
                    try {
                        $test->$method();
                        echo ".";
                        $passedTests++;
                    } catch (Exception $e) {
                        echo "F";
                        echo "\nFailed test: " . get_class($test) . "::" . $method . "\n";
                        echo "Error: " . $e->getMessage() . "\n\n";
                    }
                }
            }
        }

        echo "\n\nTests completed: $passedTests/$totalTests passed.\n";
    }
}

function assertEquals($expected, $actual, $message = "") {
    if ($expected !== $actual) {
        throw new Exception($message . " Expected: " . var_export($expected, true) . ", but got: " . var_export($actual, true));
    }
}

function assertTrue($condition, $message = "") {
    if (!$condition) {
        throw new Exception($message . " Expected true, but got false");
    }
}

function assertFalse($condition, $message = "") {
    if ($condition) {
        throw new Exception($message . " Expected false, but got true");
    }
}
<?php
require_once __DIR__ . '/TestRunner.php';
require_once __DIR__ . '/ValidatorTest.php';

$runner = new TestRunner();
$runner->addTest('ValidatorTest');

$runner->run();
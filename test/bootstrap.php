<?php
define('TEST_BASE_DIR', __DIR__);
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('BaleenTest\\', __DIR__);

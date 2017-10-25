<?php
$loader = new Phalcon\Loader();

// We're a registering a set of directories taken from the configuration file
$loader->registerDirs(
    [
        APPLICATION_PATH . $config->application->libraryDir,
    ]
)->register();
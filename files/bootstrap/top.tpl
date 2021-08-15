<?php
require("vendor/autoload.php");

// performs environment detection
$environment = getenv("ENVIRONMENT");
if (!$environment) {
    die("Value of environment variable 'ENVIRONMENT' could not be detected!");
}
define("ENVIRONMENT", $environment);

// handles STDERR flow
new Lucinda\STDERR\FrontController("stderr.xml", ENVIRONMENT, __DIR__, new Lucinda\Project\EmergencyHandler());

// handles STDOUT flow

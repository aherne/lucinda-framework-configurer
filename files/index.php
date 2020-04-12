<?php
require __DIR__ . '/vendor/autoload.php';

// performs environment detection
$environment = getenv("ENVIRONMENT");
if (!$environment) {
    die("Value of environment variable 'ENVIRONMENT' could not be detected!");
}
define("ENVIRONMENT", $environment);

// takes control of STDERR
require("application/models/EmergencyHandler.php");
new Lucinda\STDERR\FrontController("stderr.xml", ENVIRONMENT, __DIR__, new EmergencyHandler());

// takes control of STDOUT
$object = new Lucinda\STDOUT\FrontController("stdout.xml", new Lucinda\Framework\Attributes(__DIR__."/application/listeners"));
$object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, "LoggingListener");
// $object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, "SQLDataSourceInjector");
// $object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, "NoSQLDataSourceInjector");
$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "ErrorListener");
// $object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "SecurityListener");
// $object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "HttpHeadersListener");
// $object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "HttpCorsListener");
// $object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "LocalizationListener");
// $object->addEventListener(Lucinda\STDOUT\EventType::RESPONSE, "HttpCachingListener");
$object->run();
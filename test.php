<?php
use Lucinda\Configurer\FeaturesValidator;

require_once __DIR__."/vendor/autoload.php";

spl_autoload_register(function ($className) {
    $namespace = "Lucinda\\Configurer\\";
    $position = strpos($className, $namespace);
    if ($position === 0) {
        $className = substr($className, strlen($namespace));
        $fileName = "src/".str_replace("\\", "/", $className).".php";
        if (file_exists($fileName)) {
            require_once $fileName;
            return;
        }
    }
});
/**
mysql> create user test@localhost identified by 'test';
mysql> create database test;
mysql> grant all on test.* to test@localhost;
 */
$object = new Lucinda\Configurer\Features\FeaturesSelector();
new FeaturesValidator($object->getFeatures());
<?php
ini_set("display_errors", 1);
error_reporting(E_ALL);
try {
    if (sizeof($argv)!=2) {
        die("Invalid syntax: option parameter is missing!");
    }
    
    // windows fix: start
    if (!function_exists("readline")) {
        function readline($prompt = '')
        {
            echo $prompt;
            return trim(fgets(STDIN));
        }
    }
    // windows fix: end
    
    // gets and validates installation folder 
    $installationFolder = str_replace(DIRECTORY_SEPARATOR, "/", dirname(dirname(dirname(__DIR__))));
    if (!file_exists($installationFolder."/stdout.xml")) {
        throw new Exception("Folder is not a lucinda project: ".$installationFolder);
    }
    
    // runs user-selected option
    $option = $argv[1];
    switch ($option) {
        case "project":
            require_once("src/Configurer.php");
            $installer = new Lucinda\Configurer\Configurer($installationFolder);
            echo "Project installed successfully at: ".$installationFolder."\n";
            break;
        case "vhost":
            require_once("src/HostCreator.php");
            $configurer = new Lucinda\Configurer\HostCreator($installationFolder);
            echo "Host created successfully! Open your browser and go to: http://".$configurer->getHostCreated()."\n";
            break;
        default:
            throw new Exception("Invalid option: ".$option);
            break;
    }
} catch (Exception $e) {
    echo "FATAL ERROR: ".$e->getMessage()."\n";
}

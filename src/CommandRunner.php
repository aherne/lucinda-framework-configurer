<?php
namespace Lucinda\Configurer;

require_once("ProjectCreator.php");
require_once("HostCreator.php");

/**
 * Executes a configuration option.
 */
class CommandRunner
{
    /**
     * Runs option command.
     *
     * @param string $command Value of option
     * @param string[] $parameters Optional parameters
     * @throws \Exception
     */
    public function run($command, $parameters)
    {
        switch ($command) {
            case "project":
                new ProjectCreator(__DIR__);
                echo "Project installed successfully at: ".__DIR__."\n";
                break;
            case "vhost":
                $configurer = new HostCreator(__DIR__);
                echo "Host created successfully! Open your browser and go to: http://".$configurer->getHostCreated()."\n";
                break;
            default:
                throw new \Exception("Invalid option: ".$command."!");
                break;
        }
    }
}

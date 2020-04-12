<?php
namespace Lucinda\Configurer;

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
        $installationFolder = dirname(dirname(dirname(dirname(__DIR__))));
        switch ($command) {
            case "project":
                new ProjectCreator($installationFolder);
                echo "Project installed successfully at: ".$installationFolder."\n";
                break;
            case "vhost":
                $configurer = new HostCreator($installationFolder);
                echo "Host created successfully! Open your browser and go to: http://".$configurer->getHostCreated()."\n";
                break;
            default:
                throw new \Exception("Invalid option: ".$command."!");
                break;
        }
    }
}

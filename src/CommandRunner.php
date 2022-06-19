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
     * @param  string $command Value of option
     * @throws \Exception
     */
    public function run(string $command): void
    {
        $installationFolder = dirname(dirname(dirname(dirname(__DIR__))));
        switch ($command) {
        case "project":
            new ProjectCreator($installationFolder);
            echo "Project installed successfully at: " . $installationFolder . "\n";
            break;
        case "vhost":
            $creator = new HostCreator($installationFolder);
            echo "Project installed successfully at: " . $creator->getHostCreated() . "\n";
            break;
        case "create-folders":
            mkdir("compilations");
            chmod("compilations", 0777);
            mkdir("compilations/checksums");
            chmod("compilations/checksums", 0777);
            break;
        default:
            throw new \Exception("Invalid option: " . $command . "!");
        }
    }
}

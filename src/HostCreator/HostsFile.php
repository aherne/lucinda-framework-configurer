<?php

namespace Lucinda\Configurer\HostCreator;

/**
 * Encapsulates operations on .hosts file storing local DNS resolutions
 */
class HostsFile
{
    private string $path;

    /**
     * @param OperatingSystemFamily $operatingSystemFamily
     * @throws \Exception
     */
    public function __construct(OperatingSystemFamily $operatingSystemFamily)
    {
        $this->setHostsFile($operatingSystemFamily);
    }

    /**
     * Sets absolute path to hosts file where local domains are registered
     *
     * @param OperatingSystemFamily $operatingSystem
     * @throws \Exception If file could not be detected
     */
    private function setHostsFile(OperatingSystemFamily $operatingSystem): void
    {
        if ($operatingSystem == OperatingSystemFamily::WINDOWS) {
            $this->path = "C:/Windows/System32/drivers/etc/hosts";
        } else {
            $this->path = "/etc/hosts";
        }
        if (!file_exists($this->path)) {
            $this->path = \readline("- Write your hosts file absolute location: ");
            if (!file_exists($this->path)) {
                throw new \Exception("Hosts file not detected!");
            }
        }
    }

    /**
     * Adds host name in hosts file based on operating system
     *
     * @param string $hostName
     * @return bool
     * @throws \Exception If not ran by superuser/root
     */
    public function addHost(string $hostName): bool
    {
        $contents = file_get_contents($this->path);
        if (!is_writable($this->path)) {
            throw new \Exception("Script must be ran by superuser/root!");
        } elseif (!str_contains($contents, "\t" . $hostName)) {
            file_put_contents($this->path, $contents . "\n127.0.0.1\t" . $hostName, FILE_APPEND);
            return true;
        } else {
            return false;
        }
    }
}

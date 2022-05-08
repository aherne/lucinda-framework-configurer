<?php

namespace Lucinda\Configurer;

use Lucinda\Configurer\HostCreator\HostsFile;
use Lucinda\Configurer\HostCreator\OperatingSystemFamily;
use Lucinda\Configurer\HostCreator\VirtualHost;
use Lucinda\Configurer\HostCreator\WebServer;

/**
 * Creates host on server machine, based on selected features, operating system and server vendor
 */
class HostCreator
{
    const DEFAULT_ENVIRONMENT = "local";
    private string $hostCreated;

    /**
     * Kick-starts host creation based on features selected by user.
     *
     * @param string $installationFolder Folder where site is installed
     * @throws \Exception If host creation fails.
     */
    public function __construct(string $installationFolder)
    {
        $operatingSystem = $this->getOperatingSystem();

        $virtualHost = new VirtualHost($installationFolder);
        $virtualHost->setDevelopmentEnvironment(self::DEFAULT_ENVIRONMENT);

        $file = new HostsFile($operatingSystem);
        $file->addHost($virtualHost->getHostName());

        $webServer = new WebServer($operatingSystem, dirname($installationFolder));
        $webServer->addVirtualHost($virtualHost);
        $webServer->restart();

        $this->hostCreated = $virtualHost->getHostName();
    }

    /**
     * Gets caller's operating system. Supported: Windows & Linux
     *
     * @return OperatingSystemFamily
     * @throws \Exception If operating system is not supported.
     */
    private function getOperatingSystem(): OperatingSystemFamily
    {
        $operatingSystemInfo = php_uname();
        if (str_contains($operatingSystemInfo, "Windows")) {
            return OperatingSystemFamily::WINDOWS;
        } elseif (str_contains($operatingSystemInfo, "Linux")) {
            return OperatingSystemFamily::LINUX;
        } elseif (str_contains($operatingSystemInfo, "Darwin")) {
            return OperatingSystemFamily::MAC;
        } else {
            throw new \Exception("Operating system not yet supported for automatic host creation: " . $operatingSystemInfo);
        }
    }

    /**
     * Gets host name created
     *
     * @return string
     */
    public function getHostCreated()
    {
        return $this->hostCreated;
    }
}
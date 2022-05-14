<?php

namespace Lucinda\Configurer\HostCreator;

/**
 * Encapsulates a virtual host by its required coordinates
 */
class VirtualHost
{
    private $siteName;
    private $hostName;
    private $developmentEnvironment;

    /**
     * @param string $installationFolder
     * @throws \Exception
     */
    public function __construct(string $installationFolder)
    {
        $this->setSiteName($installationFolder);
        $this->setHostName();
    }

    /**
     * Detects current project folder in WEBROOT
     *
     * @param string $installationFolder
     * @return void
     * @example example.com if installation folder is /var/www/html/example.conf
     */
    private function setSiteName(string $installationFolder): void
    {
        $this->siteName = str_replace(dirname($installationFolder) . DIRECTORY_SEPARATOR, "", $installationFolder);
    }

    /**
     * Gets current project folder in WEBROOT
     *
     * @return string
     */
    public function getSiteName(): string
    {
        return $this->siteName;
    }

    /**
     * Asks user to supply a hostname matching current project
     *
     * @return void
     * @throws \Exception
     */
    private function setHostName(): void
    {
        $this->hostName = \readline("-  Write your domain name: ");
        preg_match("/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/", $this->hostName, $matches);
        if (empty($matches)) {
            throw new \Exception("Host name invalid or empty!");
        }
    }

    /**
     * Gets hostname chosen by user for current project
     *
     * @return string
     */
    public function getHostName(): string
    {
        return $this->hostName;
    }

    /**
     * Sets name of development environment matching current project.
     *
     * @return void
     */
    public function setDevelopmentEnvironment(string $developmentEnvironment): void
    {
        $this->developmentEnvironment = $developmentEnvironment;
    }

    /**
     * Gets name of development environment matching current project.
     *
     * @return string
     */
    public function getDevelopmentEnvironment(): string
    {
        return $this->developmentEnvironment;
    }
}
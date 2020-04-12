<?php
namespace Lucinda\Configurer;

/**
 * Encapsulates a virtual host
 */
abstract class AbstractVirtualHost
{
    protected $siteName;
    protected $documentRoot;
    protected $hostName;

    /**
     * Kick starts virtual host creation by site name, document root and host name
     * @param string $siteName
     * @param string $documentRoot
     * @param string $hostName
     */
    public function __construct($siteName, $documentRoot, $hostName)
    {
        $this->siteName = $siteName;
        $this->documentRoot = $documentRoot;
        $this->hostName = $hostName;
        $virtualHostFile = $this->getVirtualHostFile();
        $this->setVirtualHost($virtualHostFile);
        $this->restartWebServer();
    }

    /**
     * Gets file storing virtual host declarations
     *
     * @return string
     */
    abstract protected function getVirtualHostFile();

    /**
     * Writes virtual host to file storing declarations
     *
     * @param string $virtualHostFile
     */
    abstract protected function setVirtualHost($virtualHostFile);

    /**
     * Restarts web server for changes to take effect
     */
    abstract protected function restartWebServer();
}

<?php

namespace Lucinda\Configurer\HostCreator;

/**
 * Gets details of PHP-FPM configuration required by NGINX usage
 */
class PhpFpm
{
    private OperatingSystemFamily $operatingSystemFamily;
    private string $configurationFile;
    private string $socketFile;
    private string $serviceName;

    /**
     * @param OperatingSystemFamily $operatingSystemFamily
     * @throws \Exception
     */
    public function __construct(OperatingSystemFamily $operatingSystemFamily)
    {
        $this->operatingSystemFamily = $operatingSystemFamily;
        $rawPhpVersion = $this->getRawPhpVersion();
        $this->setConfigurationFile($rawPhpVersion);
        $this->setSocketFile($rawPhpVersion);
        $this->setServiceName($rawPhpVersion);
    }

    /**
     * Gets basic NR.NR PHP version
     *
     * @return string
     */
    private function getRawPhpVersion(): string
    {
        $phpVersion = phpversion();
        return substr($phpVersion, 0, strrpos($phpVersion, "."));
    }

    /**
     * Searches for PHP-FPM configuration file and records location
     *
     * @param string $rawPhpVersion
     * @return void
     * @throws \Exception
     */
    private function setConfigurationFile(string $rawPhpVersion): void
    {
        if ($this->operatingSystemFamily == OperatingSystemFamily::LINUX) {
            $this->configurationFile = "/etc/php/" . $rawPhpVersion . "/fpm/php-fpm.conf";
        } else {
            // TODO: add MAC/WIN support
        }
        if (!$this->configurationFile || !file_exists($this->configurationFile)) {
            throw new \Exception("PHP-FPM is required for current PHP version!");
        }
    }

    /**
     * Gets path to PHP-FPM configuration file found
     *
     * @return string
     */
    public function getConfigurationFile(): string
    {
        return $this->configurationFile;
    }

    /**
     * Searches for PHP-FPM socket file and records location
     *
     * @param string $rawPhpVersion
     * @return void
     * @throws \Exception
     */
    private function setSocketFile(string $rawPhpVersion): void
    {
        if ($this->operatingSystemFamily == OperatingSystemFamily::LINUX) {
            $this->socketFile = "/var/run/php/php" . $rawPhpVersion . "-fpm.sock";
        } else {
            // TODO: add MAC/WIN support
        }
        if (!$this->socketFile || !file_exists($this->socketFile)) {
            throw new \Exception("PHP-FPM is required for current PHP version!");
        }
    }

    /**
     * Gets path to PHP-FPM socket file found
     *
     * @return string
     */
    public function getSocketFile(): string
    {
        return $this->socketFile;
    }

    /**
     * Sets name of PHP-FPM service
     *
     * @param string $rawPhpVersion
     * @return void
     */
    private function setServiceName(string $rawPhpVersion): void
    {
        if ($this->operatingSystemFamily == OperatingSystemFamily::LINUX) {
            $this->serviceName = "php".$rawPhpVersion."-fpm";
        } elseif ($this->operatingSystemFamily == OperatingSystemFamily::MAC) {
            $this->serviceName = "php".str_replace(".", "", $rawPhpVersion);
        } else {
            // TODO: add WIN support
        }
    }

    /**
     * Gets name of PHP-FPM service
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return $this->serviceName;
    }
}

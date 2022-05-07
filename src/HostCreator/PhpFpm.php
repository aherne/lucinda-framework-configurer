<?php

namespace Lucinda\Configurer\HostCreator;

/**
 * Gets details of PHP-FPM configuration required by NGINX usage
 */
class PhpFpm
{
    private string $configurationFile;
    private string $socketFile;

    /**
     * @param OperatingSystemFamily $operatingSystemFamily
     * @throws \Exception
     */
    public function __construct(OperatingSystemFamily $operatingSystemFamily)
    {
        $rawPhpVersion = $this->getRawPhpVersion();
        $this->setConfigurationFile($operatingSystemFamily, $rawPhpVersion);
        $this->setSocketFile($operatingSystemFamily, $rawPhpVersion);
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
     * @param OperatingSystemFamily $operatingSystemFamily
     * @param string $rawPhpVersion
     * @return void
     * @throws \Exception
     */
    private function setConfigurationFile(OperatingSystemFamily $operatingSystemFamily, string $rawPhpVersion): void
    {
        if ($operatingSystemFamily == OperatingSystemFamily::LINUX) {
            $this->configurationFile = "/etc/php/" . $rawPhpVersion . "/fpm/php-fpm.conf";
        } else {
            // TODO: add MAC support
        }
        if (!file_exists($this->configurationFile)) {
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
     * @param OperatingSystemFamily $operatingSystemFamily
     * @param string $rawPhpVersion
     * @return void
     * @throws \Exception
     */
    private function setSocketFile(OperatingSystemFamily $operatingSystemFamily, string $rawPhpVersion): void
    {
        if ($operatingSystemFamily == OperatingSystemFamily::LINUX) {
            $this->socketFile = "/var/run/php/php" . $rawPhpVersion . "-fpm.sock";
        } else {
            // TODO: add MAC support
        }
        if (!file_exists($this->socketFile)) {
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

}
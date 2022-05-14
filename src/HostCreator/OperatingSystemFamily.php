<?php

namespace Lucinda\Configurer\HostCreator;

/**
 * Defines list of supported operating systems
 */
interface OperatingSystemFamily
{
    const LINUX = 1;
    const WINDOWS = 2;
    const MAC = 3;
}
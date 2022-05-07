<?php

namespace Lucinda\Configurer\HostCreator;

/**
 * Defines list of supported operating systems
 */
enum OperatingSystemFamily
{
    case LINUX;
    case WINDOWS;
    case MAC;
}
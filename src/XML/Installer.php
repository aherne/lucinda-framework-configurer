<?php

namespace Lucinda\Configurer\XML;

use Lucinda\Configurer\Features\Features;

/**
 * Abstracts XML files configuration and saving based on user choices.
 */
abstract class Installer
{
    public const DEFAULT_ENVIRONMENT = "local";
    public const DEFAULT_VERSION = "0.0.2";

    protected \SimpleXMLElement $xml;
    protected Features $features;
    protected string $xmlFilePath;

    /**
     * Sets up XML to write
     *
     * @param Features $features
     * @param string $xmlFilePath
     */
    public function __construct(Features $features, string $xmlFilePath)
    {
        $this->xmlFilePath = $xmlFilePath;
        $this->features = $features;
        $this->generateXML();
        $this->saveFile();
    }

    /**
     * Populates <resolvers> tag @ stderr.xml/stdout.xml
     */
    protected function setResolversTag(): void
    {
        $application = $this->xml->addChild("resolvers");

        if (!$this->features->isREST) {
            $html = $application->addChild("resolver");
            $html->addAttribute("format", "html");
            $html->addAttribute("content_type", "text/html");
            $html->addAttribute("class", "Lucinda\Project\ViewResolvers\Html");
            $html->addAttribute("charset", "UTF-8");
        }

        $json = $application->addChild("resolver");
        $json->addAttribute("format", "json");
        $json->addAttribute("content_type", "application/json");
        $json->addAttribute("class", "Lucinda\Project\ViewResolvers\Json");
        $json->addAttribute("charset", "UTF-8");

        $console = $application->addChild("resolver");
        $console->addAttribute("format", "console");
        $console->addAttribute("content_type", "text/plain");
        $console->addAttribute("class", "Lucinda\Project\ViewResolvers\Console");
        $console->addAttribute("charset", "UTF-8");
    }

    /**
     * Populates XML based on user choices
     */
    abstract protected function generateXML(): void;

    /**
     * Overrides project XML with one compiled here.
     */
    protected function saveFile(): void
    {
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($this->xml->asXML());
        $domxml->save($this->xmlFilePath);
    }
}

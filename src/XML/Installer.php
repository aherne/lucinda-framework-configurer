<?php
namespace Lucinda\Configurer\XML;

use Lucinda\Configurer\Features\Features;

/**
 * Abstracts XML files configuration and saving based on user choices.
 */
abstract class Installer
{
    const DEFAULT_ENVIRONMENT = "local";
    
    /**
     * @var \SimpleXMLElement
     */
    protected $xml;
    
    /**
     * @var Features
     */
    protected $features;
    
    /**
     * @var string
     */
    protected $xmlFilePath;
    
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

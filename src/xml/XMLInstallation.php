<?php
namespace Lucinda\Configurer;

/**
 * Abstracts XML files configuration and saving based on user choices.
 */
abstract class XMLInstallation
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
    
    public function __construct(Features $features, $xmlFilePath)
    {
        $this->features = $features;
        $this->generateXML();
        $this->saveFile($xmlFilePath);
    }
    
    /**
     * Populates XML based on user choices
     */
    abstract protected function generateXML();    
    
    /**
     * Overrides project XML with one compiled here.
     *
     * @param string $xmlFilePath Absolute path of XML file name to override
     */
    protected function saveFile($xmlFilePath)
    {
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($this->xml->asXML());
        $domxml->save($xmlFilePath);
    }
}

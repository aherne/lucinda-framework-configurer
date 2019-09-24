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
    
    /**
     * @var string
     */
    protected $xmlFilePath;
    
    public function __construct(Features $features, $xmlFilePath)
    {
        $this->xmlFilePath = $xmlFilePath;
        $this->features = $features;
        $this->generateXML();
        $this->saveFile();
    }
    
    /**
     * Populates XML based on user choices
     */
    abstract protected function generateXML();
    
    /**
     * Overrides project XML with one compiled here.
     */
    protected function saveFile()
    {
        $domxml = new \DOMDocument('1.0');
        $domxml->preserveWhiteSpace = false;
        $domxml->formatOutput = true;
        $domxml->loadXML($this->xml->asXML());
        $domxml->save($this->xmlFilePath);
    }
}

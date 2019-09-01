<?php
namespace Lucinda\Configurer;

require_once("XMLInstallation.php");

/**
 * Populates stderr.xml based on features selected by user
 */
class StderrXMLInstallation
{
    protected function generateXML()
    {
        $this->xml = simplexml_load_file($installationFolder.DIRECTORY_SEPARATOR."stderr.xml");
        $this->setApplication();
        $this->setReporter();
        $this->setRenderer();
    }
    
    /**
     * Sets <application> tag @ stderr.xml
     */
    private function setApplication()
    {
        $this->xml->application["default_content_type"] = ($this->features->siteType=="normal"?"text/html":"application/json");
        if ($this->features->templating) {
            $this->xml->application->addAttribute("templates_extension", "html");
            $this->xml->application->paths->addChild("compilations", "compilations");
            $this->xml->application->paths->addChild("tags", "application/tags");
        }
        $this->xml->application->display_errors->local = 1;
    }

    /**
     * Populates <reporters> tag @ stderr.xml
     */
    private function setReporter()
    {
        $reporter = $this->xml->addChild("reporters")->addChild(self::DEFAULT_ENVIRONMENT)->addChild("reporter");
        $reporter->addAttribute("class", "FileReporter");
        $reporter->addAttribute("path", "errors");
        $reporter->addAttribute("format", "%d %f %l %m");
    }
    
    /**
     * Sets HTML renderer in <renderers> tag @ stderr.xml
     */
    private function setRenderer()
    {
        if ($this->features->templating) {
            $this->xml->renderers->renderer[0]["class"]= "ViewLanguageRenderer";
        } else {
            $this->xml->renderers->renderer[0]["class"]= "HtmlRenderer";
        }
    }
}

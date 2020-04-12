<?php
namespace Lucinda\Configurer\XML;

/**
 * Populates stderr.xml based on features selected by user
 */
class StderrInstaller extends Installer
{
    /**
     * {@inheritDoc}
     * @see Installer::generateXML()
     */
    protected function generateXML()
    {
        $this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><!DOCTYPE xml><xml></xml>');
        $this->setApplicationTag();
        $this->setResolversTag();
        $this->setReportersTag();
        $this->setExceptionsTag();
    }
    
    /**
     * Populates <application> tag @ stderr.xml
     */
    private function setApplicationTag()
    {
        $application = $this->xml->addChild("application");
        $application->addAttribute("version", "0.0.1");
        $application->addAttribute("default_format", (!$this->features->isREST?"html":"json"));
        if ($this->features->security) {
            $application->addAttribute("redirect", "1");
        }
        $paths = $application->addChild("paths");
        $paths->addAttribute("controllers", "application/controllers");
        $paths->addAttribute("resolvers", "application/renderers");
        $paths->addAttribute("reporters", "application/reporters");
        if (!$this->features->isREST) {
            $paths->addAttribute("views", "application/views");
        }
        $application->display_errors->local = 1;
    }
    
    /**
     * Populates <resolvers> tag @ stderr.xml
     */
    private function setResolversTag()
    {
        $application = $this->xml->addChild("resolvers");
        
        if (!$this->features->isREST) {
            $html = $application->addChild("resolver");
            $html->addAttribute("name", "html");
            $html->addAttribute("content_type", "text/html");
            $html->addAttribute("class", "HtmlRenderer");
            $html->addAttribute("charset", "UTF-8");
        }
        
        $json = $application->addChild("format");
        $json->addAttribute("name", "json");
        $json->addAttribute("content_type", "application/json");
        $json->addAttribute("class", "JsonRenderer");
        $json->addAttribute("charset", "UTF-8");
    }

    /**
     * Populates <reporters> tag @ stderr.xml
     */
    private function setReportersTag()
    {
        $reporter = $this->xml->addChild("reporters")->addChild(self::DEFAULT_ENVIRONMENT)->addChild("reporter");
        $reporter->addAttribute("class", "FileReporter");
        $reporter->addAttribute("path", "errors");
        $reporter->addAttribute("format", "%d %f %l %m");
    }
    
    /**
     * Populates <exceptions> tag @ stderr.xml
     */
    private function setExceptionsTag()
    {
        $routes = $this->xml->addChild("exceptions");
        $routes->addAttribute("controller", "ErrorsController");
        $routes->addAttribute("error_type", "LOGICAL");
        $routes->addAttribute("http_status", "500");
        foreach ($this->features->exceptions->exceptions as $info) {
            $route = $routes->addChild("exception");
            $route->addAttribute("class", $info->class);
            $route->addAttribute("error_type", $info->error_type);
            if ($info->controller!==null) {
                $route->addAttribute("controller", $info->controller);
            }
            if ($info->view!==null) {
                $route->addAttribute("view", $info->view);
            }
            if ($info->http_status!==null) {
                $route->addAttribute("http_status", $info->http_status);
            }
        }
    }
}
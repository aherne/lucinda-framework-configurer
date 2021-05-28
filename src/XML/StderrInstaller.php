<?php
namespace Lucinda\Configurer\XML;

/**
 * Populates stderr.xml based on features selected by user
 */
class StderrInstaller extends Installer
{
    const DEFAULT_ROUTE = "default";
    
    /**
     * {@inheritDoc}
     * @see Installer::generateXML()
     */
    protected function generateXML(): void
    {
        $this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><!DOCTYPE xml><xml></xml>');
        $this->setApplicationTag();
        $this->setDisplayErrorsTag();
        $this->setResolversTag();
        $this->setReportersTag();
        $this->setTemplatingTag();
        $this->setRoutesTag();
    }
    
    /**
     * Populates <application> tag @ stderr.xml
     */
    private function setApplicationTag(): void
    {
        $application = $this->xml->addChild("application");
        $application->addAttribute("version", self::DEFAULT_VERSION);
        $application->addAttribute("default_format", (!$this->features->isREST?"html":"json"));
        $application->addAttribute("default_route", self::DEFAULT_ROUTE);
        if ($this->features->security) {
            $application->addAttribute("redirect", "1");
        }
    }
    
    /**
     * Populates <display_errors> tag @ stderr.xml
     */
    private function setDisplayErrorsTag(): void
    {
        $application = $this->xml->addChild("display_errors");
        $application->addChild(self::DEFAULT_ENVIRONMENT, 1);
    }
    
    /**
     * Populates <resolvers> tag @ stderr.xml
     */
    private function setResolversTag(): void
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
    }

    /**
     * Populates <reporters> tag @ stderr.xml
     */
    private function setReportersTag(): void
    {
        $reporter = $this->xml->addChild("reporters")->addChild(self::DEFAULT_ENVIRONMENT)->addChild("reporter");
        $reporter->addAttribute("class", "Lucinda\Project\ErrorReporters\File");
        $reporter->addAttribute("path", "errors");
        $reporter->addAttribute("format", "%d %f %l %m");
    }
    
    /**
     * Populates <templating> tag @ stdout.xml
     */
    private function setTemplatingTag(): void
    {
        if ($this->features->isREST) {
            return;
        }
        
        $templating = $this->xml->addChild("templating");
        $templating->addAttribute("compilations_path", "compilations");
        $templating->addAttribute("tags_path", "templates/tags");
        $templating->addAttribute("templates_path", "templates/views");
        $templating->addAttribute("templates_extension", "html");
    }
    
    /**
     * Populates <exceptions> tag @ stderr.xml
     */
    private function setRoutesTag(): void
    {
        $routes = $this->xml->addChild("routes");
        
        $route = $routes->addChild("route");
        $route->addAttribute("id", self::DEFAULT_ROUTE);
        $route->addAttribute("controller", "Lucinda\Project\Controllers\Error");
        $route->addAttribute("error_type", "LOGICAL");
        $route->addAttribute("http_status", "500");
        
        foreach ($this->features->exceptions->exceptions as $info) {
            $route = $routes->addChild("route");
            $route->addAttribute("id", $info->class);
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

<?php
namespace Lucinda\Configurer\Code;

use Lucinda\Configurer\Features\Features;

/**
 * Sets up PHP files and dependencies based on selected features.
 */
class Installer
{
    private $rootFolder;
    private $features;

    /**
     * @param Features $features
     */
    public function __construct(Features $features, string $installationFolder)
    {
        $this->rootFolder = $installationFolder;
        $this->features = $features;
        $this->createFolders();
        $this->createControllers();
        $this->createModels();
        $this->createViews();
        $this->createPublic();
        $this->createBootstrap();
    }
    
    /**
     * Creates project folders according to features selected
     */
    private function createFolders(): void
    {
        $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."controllers");
        $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."validators");
                
        if (!$this->features->isREST) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."views");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."header");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."compilations");
        }
        
        if ($this->features->internationalization) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."locale");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."locale".DIRECTORY_SEPARATOR.$this->features->internationalization->defaultLocale);
        }
        
        if ($this->features->security && ($this->features->security->authenticationMethod!=2 || $this->features->security->authorizationMethod!=1)) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR."dao");
        }
        
        $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."public");
    }
    
    /**
     * Creates project controllers according to features selected
     */
    private function createControllers(): void
    {
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR.($this->features->isREST?"rest":"no_rest");
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."controllers";
        
        copy($sourceFolder.DIRECTORY_SEPARATOR."ErrorsController.php", $destinationFolder.DIRECTORY_SEPARATOR."ErrorsController.php");
        
        $controllerBody = file_get_contents($sourceFolder.DIRECTORY_SEPARATOR."IndexController.php");
        $controllerBody = str_replace("{FEATURES}", json_encode($this->features), $controllerBody);
        file_put_contents($destinationFolder.DIRECTORY_SEPARATOR."IndexController.php", $controllerBody);
        
        if ($this->features->security) {
            copy($sourceFolder.DIRECTORY_SEPARATOR."LoginController.php", $destinationFolder.DIRECTORY_SEPARATOR."LoginController.php");
            copy($sourceFolder.DIRECTORY_SEPARATOR."MembersController.php", $destinationFolder.DIRECTORY_SEPARATOR."MembersController.php");
            copy($sourceFolder.DIRECTORY_SEPARATOR."SecurityPacketController.php", $destinationFolder.DIRECTORY_SEPARATOR."SecurityPacketController.php");
            if ($this->features->security->isCMS) {
                copy($sourceFolder.DIRECTORY_SEPARATOR."RestrictedController.php", $destinationFolder.DIRECTORY_SEPARATOR."RestrictedController.php");
            }
        }
    }
    
    /**
     * Creates project models according to features selected
     */
    private function createModels(): void
    {
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."models";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."models";
        
        copy(
            $sourceFolder.DIRECTORY_SEPARATOR."EmergencyHandler".($this->features->isREST?"2":"1").".php", 
            $destinationFolder.DIRECTORY_SEPARATOR."EmergencyHandler.php"
            );
        
        if (!$this->features->security || ($this->features->security->authenticationMethod==2 && $this->features->security->authorizationMethod==1)) {
            return;
        }
        
        $increment = 0;
        if ($this->features->security->authenticationMethod==0) {
            $increment = ($this->features->security->authorizationMethod==0?1:($this->features->security->isCMS?3:5));
        } else if ($this->features->security->authenticationMethod==1) {
            $increment = ($this->features->security->authorizationMethod==0?2:($this->features->security->isCMS?4:6));
        } else if ($this->features->security->authorizationMethod==0) {
            $increment = ($this->features->security->isCMS?7:8);
        }
        if ($increment) {
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersFormAuthentication".$increment.".php",
                $destinationFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersFormAuthentication.php"
                );
        }
        
        if ($this->features->security->authenticationMethod==1) {
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersOAuth2Authentication.php",
                $destinationFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersOAuth2Authentication.php"
                );
        }
        
        if ($this->features->security->authorizationMethod==0) {
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."PagesAuthorization.php", 
                $destinationFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."PagesAuthorization.php"
                );
            $increment = ($this->features->security->isCMS?1:2);
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersAuthorization".$increment.".php",
                $destinationFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersAuthorization.php"
                );
        }
    }
    
    /**
     * Creates project views according to features selected
     */
    private function createViews(): void
    {        
        if ($this->features->isREST) {
            return;
        }
        
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."views";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."views";
        copy($sourceFolder.DIRECTORY_SEPARATOR."debug.html", $destinationFolder.DIRECTORY_SEPARATOR."debug.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."404.html", $destinationFolder.DIRECTORY_SEPARATOR."404.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."405.html", $destinationFolder.DIRECTORY_SEPARATOR."405.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."500.html", $destinationFolder.DIRECTORY_SEPARATOR."500.html");
        if (!$this->features->security) {
            copy($sourceFolder.DIRECTORY_SEPARATOR."index.html", $destinationFolder.DIRECTORY_SEPARATOR."index.html");
        } else {
            $subfolder = ($this->features->security->isCMS?"cms":"no_cms");
            copy($sourceFolder.DIRECTORY_SEPARATOR.$subfolder.DIRECTORY_SEPARATOR."index.html", $destinationFolder.DIRECTORY_SEPARATOR."index.html");
            copy($sourceFolder.DIRECTORY_SEPARATOR.$subfolder.DIRECTORY_SEPARATOR."login.html", $destinationFolder.DIRECTORY_SEPARATOR."login.html");
            copy($sourceFolder.DIRECTORY_SEPARATOR.$subfolder.DIRECTORY_SEPARATOR."members.html", $destinationFolder.DIRECTORY_SEPARATOR."members.html");
            if ($this->features->security->isCMS) {
                copy($sourceFolder.DIRECTORY_SEPARATOR.$subfolder.DIRECTORY_SEPARATOR."restricted.html", $destinationFolder.DIRECTORY_SEPARATOR."restricted.html");
            }
            copy($sourceFolder.DIRECTORY_SEPARATOR."400.html", $destinationFolder.DIRECTORY_SEPARATOR."400.html");
            copy($sourceFolder.DIRECTORY_SEPARATOR."401.html", $destinationFolder.DIRECTORY_SEPARATOR."401.html");
            copy($sourceFolder.DIRECTORY_SEPARATOR."403.html", $destinationFolder.DIRECTORY_SEPARATOR."403.html");
        }
        
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."tags";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags";
        copy($sourceFolder.DIRECTORY_SEPARATOR."header".DIRECTORY_SEPARATOR."status.html", $destinationFolder.DIRECTORY_SEPARATOR."header".DIRECTORY_SEPARATOR."status.html");  
        copy($sourceFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."bottom.html", $destinationFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."bottom.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."feature.html", $destinationFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."feature.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."top.html", $destinationFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."top.html");
    }
    
    /**
     * Creates project public files according to features selected
     */
    private function createPublic(): void
    {
        if ($this->features->isREST) {
            return;
        }
        
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."public";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."public";
        copy($sourceFolder.DIRECTORY_SEPARATOR."debug.css", $destinationFolder.DIRECTORY_SEPARATOR."debug.css");
    }
    
    /**
     * Creates project bootstrap according to features selected
     */
    private function createBootstrap(): void
    {
        $sourceFile = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."index.php";
        $destinationFile = $this->rootFolder.DIRECTORY_SEPARATOR."index.php";
        
        $contents = file_get_contents($sourceFile);
        $contents = preg_replace_callback('/\\n\/\/\s+\$object->addEventListener\([^"]+"([^"]+)"\);/', function($matches){
            switch(matches[1]) {
                case "SQLDataSourceInjector":
                    return ($this->features->sqlServer?str_replace("// ", "", $matches[0]):"");
                    break;
                case "NoSQLDataSourceInjector":
                    return ($this->features->nosqlServer?str_replace("// ", "", $matches[0]):"");
                    break;
                case "SecurityListener":
                    return ($this->features->security?str_replace("// ", "", $matches[0]):"");
                    break;
                case "HttpHeadersListener":
                    return ($this->features->headers?str_replace("// ", "", $matches[0]):"");
                    break;
                case "HttpCorsListener":
                    return ($this->features->headers->cors?str_replace("// ", "", $matches[0]):"");
                    break;
                case "LocalizationListener":
                    return ($this->features->internationalization?str_replace("// ", "", $matches[0]):"");
                    break;
                case "HttpCachingListener":
                    return ($this->features->headers->caching?str_replace("// ", "", $matches[0]):"");
                    break;
            }
        }, $contents);
        file_put_contents($destinationFile, $contents);
    }
    
    /**
     * Creates a folder in project by name
     * 
     * @param string $folder
     */
    private function makeFolder(string $folder): void
    {
        if (!file_exists($folder)) {
            mkdir($folder);
            chmod($folder, 0777);
        }
    }
}

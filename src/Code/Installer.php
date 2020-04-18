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
        $this->saveFeatures();
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
        
        if ($this->features->security) {
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
        copy($sourceFolder.DIRECTORY_SEPARATOR."IndexController.php", $destinationFolder.DIRECTORY_SEPARATOR."IndexController.php");
        if ($this->features->security) {
            copy($sourceFolder.DIRECTORY_SEPARATOR."LoginController.php", $destinationFolder.DIRECTORY_SEPARATOR."LoginController.php");
            copy($sourceFolder.DIRECTORY_SEPARATOR."SecurityPacketController.php", $destinationFolder.DIRECTORY_SEPARATOR."SecurityPacketController.php");
            if ($this->features->security->isCMS) {
                copy($sourceFolder.DIRECTORY_SEPARATOR."RestrictedController.php", $destinationFolder.DIRECTORY_SEPARATOR."RestrictedController.php");
            } else {
                copy($sourceFolder.DIRECTORY_SEPARATOR."MembersController.php", $destinationFolder.DIRECTORY_SEPARATOR."MembersController.php");
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
        
        if ($this->features->security) {
            if ($this->features->nosqlServer) {
                copy(
                    $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."throttlers".DIRECTORY_SEPARATOR."NoSqlLoginThrottler.php",
                    $destinationFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."NoSqlLoginThrottler.php"
                    );
            } else {
                copy(
                    $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."throttlers".DIRECTORY_SEPARATOR."SqlLoginThrottler.php",
                    $destinationFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."SqlLoginThrottler.php"
                    );
            }
        }
        
        if (!$this->features->security || ($this->features->security->authenticationMethod==2 && $this->features->security->authorizationMethod==1)) {
            return;
        }
        
        $increment = 0;
        if ($this->features->security->authenticationMethod==0) {
            $increment = ($this->features->security->authorizationMethod==0?1:($this->features->security->isCMS?3:5));
        } elseif ($this->features->security->authenticationMethod==1) {
            $increment = ($this->features->security->authorizationMethod==0?2:($this->features->security->isCMS?4:6));
        } elseif ($this->features->security->authorizationMethod==0) {
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
            if ($this->features->security->isCMS) {
                copy($sourceFolder.DIRECTORY_SEPARATOR.$subfolder.DIRECTORY_SEPARATOR."restricted.html", $destinationFolder.DIRECTORY_SEPARATOR."restricted.html");
            } else {
                copy($sourceFolder.DIRECTORY_SEPARATOR.$subfolder.DIRECTORY_SEPARATOR."members.html", $destinationFolder.DIRECTORY_SEPARATOR."members.html");
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
        copy($sourceFolder.DIRECTORY_SEPARATOR."default.css", $destinationFolder.DIRECTORY_SEPARATOR."default.css");
    }
    
    /**
     * Creates project bootstrap according to features selected
     */
    private function createBootstrap(): void
    {
        $sourceFile = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."index.php";
        $destinationFile = $this->rootFolder.DIRECTORY_SEPARATOR."index.php";
        
        $contents = file_get_contents($sourceFile);
        $position = strrpos($contents, '$object->run();');
        $addition = "";
        if ($this->features->logging) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, "LoggingListener");'."\n";
        }
        if ($this->features->sqlServer) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, "SQLDataSourceInjector");'."\n";
        }
        if ($this->features->nosqlServer) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, "NoSQLDataSourceInjector");'."\n";
        }
        $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "ErrorListener");'."\n";
        if ($this->features->security) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "SecurityListener");'."\n";
        }
        if ($this->features->internationalization) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "LocalizationListener");'."\n";
        }
        if ($this->features->headers) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "HttpHeadersListener");'."\n";
            if ($this->features->headers->cors) {
                $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, "HttpCorsListener");'."\n";
            }
            if ($this->features->headers->caching) {
                $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::RESPONSE, "HttpCachingListener");'."\n";
            }
        }
        file_put_contents($destinationFile, substr($contents, 0, $position).$addition.'$object->run();');
    }
    
    /**
     * Saves features to disk
     */
    private function saveFeatures(): void
    {
        $destinationFile = $this->rootFolder.DIRECTORY_SEPARATOR."features.json";
        file_put_contents($destinationFile, json_encode($this->features));
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

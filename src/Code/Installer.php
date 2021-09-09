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
        $this->createBootstrap();
        $this->saveFeatures();
    }
    
    /**
     * Creates project folders according to features selected
     */
    private function createFolders(): void
    {
        $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."Validators");
                
        if (!$this->features->isREST) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."tags");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site");
        }
        
        if ($this->features->internationalization) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."locale");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."locale".DIRECTORY_SEPARATOR.$this->features->internationalization->defaultLocale);
        }
        
        $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."public");
    }
    
    /**
     * Creates project controllers according to features selected
     */
    private function createControllers(): void
    {
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."controllers".DIRECTORY_SEPARATOR.($this->features->isREST?"rest":"no_rest");
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR."Controllers";
        
        copy($sourceFolder.DIRECTORY_SEPARATOR."Index.php", $destinationFolder.DIRECTORY_SEPARATOR."Index.php");
        if ($this->features->security) {
            copy($sourceFolder.DIRECTORY_SEPARATOR."Login.php", $destinationFolder.DIRECTORY_SEPARATOR."Login.php");
            if ($this->features->security->isCMS) {
                copy($sourceFolder.DIRECTORY_SEPARATOR."Restricted.php", $destinationFolder.DIRECTORY_SEPARATOR."Restricted.php");
            } else {
                copy($sourceFolder.DIRECTORY_SEPARATOR."Members.php", $destinationFolder.DIRECTORY_SEPARATOR."Members.php");
            }
        }
    }
    
    /**
     * Creates project models according to features selected
     */
    private function createModels(): void
    {
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."models";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."src";
               
        // if security is not enabled, do nothing
        if (!$this->features->security) {
            return;
        }
        
        // if authentication & authorization are done based on ACL, do nothing
        if ($this->features->security->authenticationMethod==2 && $this->features->security->authorizationMethod==1) {
            return;
        }
        
        // install data access objects based on user selections
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
                $destinationFolder.DIRECTORY_SEPARATOR."DAO".DIRECTORY_SEPARATOR."UsersFormAuthentication.php"
            );
        }
        if ($this->features->security->authenticationMethod==1) {
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersOAuth2Authentication.php",
                $destinationFolder.DIRECTORY_SEPARATOR."DAO".DIRECTORY_SEPARATOR."UsersOAuth2Authentication.php"
            );
        }
        if ($this->features->security->authorizationMethod==0) {
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."PagesAuthorization.php",
                $destinationFolder.DIRECTORY_SEPARATOR."DAO".DIRECTORY_SEPARATOR."PagesAuthorization.php"
            );
            $increment = ($this->features->security->isCMS?1:2);
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."UsersAuthorization".$increment.".php",
                $destinationFolder.DIRECTORY_SEPARATOR."DAO".DIRECTORY_SEPARATOR."UsersAuthorization.php"
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
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."views";
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
        }
        
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."tags";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."tags";
        copy($sourceFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."status.html", $destinationFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."status.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."header.html", $destinationFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."header.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."footer.html", $destinationFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."footer.html");
        copy($sourceFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."welcome.html", $destinationFolder.DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."welcome.html");
        
        $sourceFolder = dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."public";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."public";
        copy($sourceFolder.DIRECTORY_SEPARATOR."default.css", $destinationFolder.DIRECTORY_SEPARATOR."default.css");
    }
    
    /**
     * Creates project bootstrap according to features selected
     */
    private function createBootstrap(): void
    {
        $destinationFile = $this->rootFolder.DIRECTORY_SEPARATOR."index.php";
        
        // detects event listeners to add based on user selections
        $eventListeners = [];
        $eventListeners["CONSOLE"]["Error"] = "APPLICATION";
        if ($this->features->logging) {
            $eventListeners["HTTP"]["Logging"] = "APPLICATION";
            $eventListeners["CONSOLE"]["Logging"] = "APPLICATION";
        }
        if ($this->features->sqlServer) {
            $eventListeners["HTTP"]["SQLDataSource"] = "APPLICATION";
            $eventListeners["CONSOLE"]["SQLDataSource"] = "APPLICATION";
        }
        if ($this->features->nosqlServer) {
            $eventListeners["HTTP"]["NoSQLDataSource"] = "APPLICATION";
            $eventListeners["CONSOLE"]["NoSQLDataSource"] = "APPLICATION";
        }
        $eventListeners["HTTP"]["Error"] = "REQUEST";
        if ($this->features->security) {
            $eventListeners["HTTP"]["Security"] = "REQUEST";
        }
        if ($this->features->internationalization) {
            $eventListeners["HTTP"]["Localization"] = "REQUEST";
        }
        if ($this->features->headers) {
            $eventListeners["HTTP"]["HttpHeaders"] = "REQUEST";
            if ($this->features->headers->cors) {
                $eventListeners["HTTP"]["HttpCors"] = "REQUEST";
            }
            if ($this->features->headers->caching) {
                $eventListeners["HTTP"]["HttpCaching"] = "RESPONSE";
            }
        }
        
        // composes bootstrap body
        $bootstrap = file_get_contents(dirname(__DIR__, 2).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR."index.tpl");
        $events = "";
        foreach ($eventListeners["HTTP"] as $name=>$type) {
            $events .= "    ".'$object->addEventListener(Lucinda\STDOUT\EventType::'.$type.', Lucinda\Project\EventListeners\\'.$name.'::class);'."\n";
        }
        $bootstrap = str_replace("(EVENTS_HTTP)", substr($events, 0, -1), $bootstrap);
        $events = "";
        foreach ($eventListeners["CONSOLE"] as $name=>$type) {
            $events .= "    ".'$object->addEventListener(Lucinda\ConsoleSTDOUT\EventType::'.$type.', Lucinda\Project\EventListeners\Console\\'.$name.'::class);'."\n";
        }
        $bootstrap = str_replace("(EVENTS_CONSOLE)", substr($events, 0, -1), $bootstrap);
        file_put_contents($destinationFile, str_replace("(EVENTS)", substr($events, 0, -1), $bootstrap));
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

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
        
        // install login throttlers
        if ($this->features->nosqlServer) {
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."NoSqlLoginThrottler.php",
                $destinationFolder.DIRECTORY_SEPARATOR."DAO".DIRECTORY_SEPARATOR."NoSqlLoginThrottler.php"
            );
        } elseif ($this->features->sqlServer) {
            copy(
                $sourceFolder.DIRECTORY_SEPARATOR."dao".DIRECTORY_SEPARATOR."SqlLoginThrottler.php",
                $destinationFolder.DIRECTORY_SEPARATOR."DAO".DIRECTORY_SEPARATOR."SqlLoginThrottler.php"
            );
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
    }
    
    /**
     * Creates project bootstrap according to features selected
     */
    private function createBootstrap(): void
    {
        $destinationFile = $this->rootFolder.DIRECTORY_SEPARATOR."index.php";
        
        $contents = file_get_contents($destinationFile);
        $position = strrpos($contents, '$object->run();');
        $addition = "";
        if ($this->features->logging) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, Lucinda\Project\EventListeners\Logging::class);'."\n";
        }
        if ($this->features->sqlServer) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, Lucinda\Project\EventListeners\SQLDataSource::class);'."\n";
        }
        if ($this->features->nosqlServer) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::APPLICATION, Lucinda\Project\EventListeners\NoSQLDataSource::class);'."\n";
        }
        if ($this->features->security) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, Lucinda\Project\EventListeners\Security::class);'."\n";
        }
        if ($this->features->internationalization) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, Lucinda\Project\EventListeners\Localization::class);'."\n";
        }
        if ($this->features->headers) {
            $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, Lucinda\Project\EventListeners\HttpHeaders::class);'."\n";
            if ($this->features->headers->cors) {
                $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::REQUEST, Lucinda\Project\EventListeners\HttpCors::class);'."\n";
            }
            if ($this->features->headers->caching) {
                $addition .= '$object->addEventListener(Lucinda\STDOUT\EventType::RESPONSE, Lucinda\Project\EventListeners\HttpCaching::class);'."\n";
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

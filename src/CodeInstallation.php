<?php
namespace Lucinda\Configurer;

/**
 * Sets up PHP files and dependencies based on selected features.
 */
class CodeInstallation
{
    private $rootFolder;
    private $features;

    /**
     * @param Features $features
     */
    public function __construct($features, $installationFolder)
    {
        $this->rootFolder = $installationFolder;
        $this->features = $features;
        $this->copyControllers();
        $this->copyModels();
        if ($features->siteType != "RESTful web services") {
            $this->copyViews();
            $this->copyPublic();
        } else {
            $this->updateBootstrap();
        }
    }

    /**
     * Copies controller files from installer to project and sets them up
     */
    private function copyControllers()
    {
        $controllers = array();
        $controllers[] = "IndexController";
        if ($this->features->security) {
            $controllers[]="LoginController";
            if ($this->features->siteType=="CMS") {
                $controllers[] = "RestrictedController";
            } else {
                $controllers[] = "MembersController";
            }
        }
        
        $sourceFolder = dirname(__DIR__).DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."controllers";
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."controllers";
        foreach ($controllers as $controller) {
            $controllerPath = $destinationFolder.DIRECTORY_SEPARATOR.$controller.".php";
            copy($sourceFolder.DIRECTORY_SEPARATOR.$controller.".php", $controllerPath);
            $controllerBody = file_get_contents($controllerPath);
            if ($controller=="IndexController") {
                $controllerBody = str_replace("{FEATURES}", json_encode($this->features), $controllerBody);
            }
            if ($this->features->siteType == "RESTful web services") {
                $controllerBody = str_replace(array("Lucinda\MVC\STDOUT\Controller", "public function run"), array("RestController", "public function GET"), $controllerBody);
            }
            file_put_contents($controllerPath, $controllerBody);
        }
    }

    /**
     * Copies models from installer to project and sets them up
     */
    private function copyModels()
    {
        if ($this->features->validation) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR."validators");
        }
        if (!$this->features->security) {
            return;
        }
        $daos = array();
        foreach ($this->features->security->authenticationMethods as $authenticationMethod) {
            switch ($authenticationMethod) {
                case "database":
                    $daos[]="UsersAuthentication";
                    break;
                case "oauth2 providers":
                    $daos[]="UsersOAuth2Authentication";
                    break;
            }
        }
        switch ($this->features->security->authorizationMethod) {
            case "database":
                $daos[]="UsersAuthorization";
                $daos[]="PagesAuthorization";
                break;
        }
        
        if (!empty($daos)) {
            $sourceFolder = dirname(__DIR__).DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR."dao";
            $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."models".DIRECTORY_SEPARATOR."dao";
            $this->makeFolder($destinationFolder);
            foreach ($daos as $dao) {
                $daoFile = $destinationFolder.DIRECTORY_SEPARATOR.$dao.".php";
                copy($sourceFolder.DIRECTORY_SEPARATOR.$dao.".php", $daoFile);
                if ($dao=="UsersAuthorization") {
                    if ($this->features->siteType=="CMS") {
                        $query="SELECT t1.id FROM roles_resources AS t1\nINNER JOIN users_roles AS t2 USING(role_id)\nWHERE t1.resource_id = :resource AND t2.user_id=:user";
                    } else {
                        $query="SELECT id FROM users_resources WHERE resource_id=:resource AND user_id=:user";
                    }
                    file_put_contents($daoFile, str_replace("{QUERY}", $query, file_get_contents($daoFile)));
                }
                if ($dao="UsersAuthentication") {
                    $contents = file_get_contents($daoFile);
                    $contents = str_replace("{QUERY}", (sizeof($this->features->security->authenticationMethods)==1?"SELECT id AS user_id, password FROM users WHERE username=:user":"SELECT user_id, password FROM users__form WHERE username=:user"), $contents);
                    if ($this->features->security->authorizationMethod=="access control list" && in_array("database", $this->features->security->authenticationMethods)) {
                        $contents = str_replace('Lucinda\WebSecurity\UserAuthenticationDAO', 'Lucinda\WebSecurity\UserAuthenticationDAO, Lucinda\WebSecurity\UserAuthorizationRoles', $contents);
                        $methodBody = "";
                        if ($this->features->siteType=="CMS") {
                            $methodBody = 'if($userID) {
            return SQL("SELECT t2.name FROM user_roles AS t1 
            INNER JOIN roles AS t2 ON t1.role_id = t2.id 
            WHERE t1.user_id=:user", array(":user"=>$userID))->toColumn();
        } else {
            return ["GUEST"]
        }';
                        } else {
                            $methodBody = 'return ($userID?["MEMBER"]:["GUEST"]);';
                        }
                        $contents = str_replace("{
    }", '{
    }
    
    public function getRoles($userID)
    {
        '.$methodBody.'
    }', $contents);
                    }
                    file_put_contents($daoFile, $contents);
                }
            }
        }
    }

    /**
     * Copies views from installer to project and sets them up
     */
    private function copyViews()
    {
        $sourceFolder = dirname(__DIR__).DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR.strtolower($this->features->siteType);
        $destinationFolder = $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."views";
        $viewExtension = ($this->features->templating?"html":"php");
        
        if ($this->features->templating) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."compilations");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."header");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site");
        }

        if ($this->features->internationalization) {
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."locale");
            $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."locale".DIRECTORY_SEPARATOR.$this->features->internationalization->defaultLocale);
        }
        
        $views = array();
        $views[] = "index";
        if ($this->features->security) {
            $views[] = "login";
            if ($this->features->siteType=="CMS") {
                $views[] = "restricted";
            } else {
                $views[] = "members";
            }
        }
        
        foreach ($views as $view) {
            copy($sourceFolder.DIRECTORY_SEPARATOR.$view.".".$viewExtension, $destinationFolder.DIRECTORY_SEPARATOR.$view.".".$viewExtension);
        }

        if ($this->features->templating) {
            copy(
                dirname(__DIR__).DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."header".DIRECTORY_SEPARATOR."status.html",
                $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."header".DIRECTORY_SEPARATOR."status.html"
                );
            copy(
                dirname(__DIR__).DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."feature.html",
                $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."feature.html"
                );
            copy(
                dirname(__DIR__).DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."top.html",
                $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."top.html"
                );
            copy(
                dirname(__DIR__).DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."bottom.html",
                $this->rootFolder.DIRECTORY_SEPARATOR."application".DIRECTORY_SEPARATOR."tags".DIRECTORY_SEPARATOR."site".DIRECTORY_SEPARATOR."bottom.html"
                );
        }
    }
    
    private function copyPublic()
    {
        $this->makeFolder($this->rootFolder.DIRECTORY_SEPARATOR."public");
        copy(
            dirname(__DIR__).DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."default.css",
            $this->rootFolder.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."default.css"
            );
    }
    
    private function updateBootstrap()
    {
        $bootstrap = $this->rootFolder.DIRECTORY_SEPARATOR."index.php";
        file_put_contents($bootstrap, str_replace('require_once("vendor/lucinda/mvc/loader.php");', 'require_once("vendor/lucinda/mvc/loader.php");
require_once("application/controllers/RestController.php");', file_get_contents($bootstrap)));
    }
    
    private function makeFolder($folder)
    {
        if (!file_exists($folder)) {
            mkdir($folder);
            chmod($folder, 0777);
        }
    }
}

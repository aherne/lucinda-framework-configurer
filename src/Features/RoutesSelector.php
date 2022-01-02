<?php
namespace Lucinda\Configurer\Features;

use Lucinda\Configurer\Features\Routes\Route;

/**
 * Sets up routes field of Features based on data already prompted by user
 */
class RoutesSelector
{
    private Routes $routes;
    private Features $features;
    
    /**
     * @param Features $features
     */
    public function __construct(Features $features)
    {
        $this->features = $features;
        
        $this->routes = new Routes();
        $this->addRoute("index");
        if ($features->security) {
            $this->routes->default_roles = $features->security->isCMS?"MEMBERS,ADMINISTRATORS":"GUESTS,MEMBERS";
            $this->addRoute("login");
            $this->addRoute("logout");
            if ($features->security->isCMS) {
                $this->addRoute("restricted");
            } else {
                $this->addRoute("members");
            }
        }
    }
    
    /**
     * Adds route based on uri
     *
     * @param string $url
     */
    private function addRoute(string $url): void
    {
        $route = new Route();
        $route->url = $url;
        // set controller & id
        switch ($url) {
            case "index":
                $route->controller = "Lucinda\Project\Controllers\Index";
                $route->id = 1;
                break;
            case "login":
                $route->controller = "Lucinda\Project\Controllers\Login";
                $route->id = 2;
                break;
            case "logout":
                $route->id = 3;
                break;
            case "members":
                $route->controller = "Lucinda\Project\Controllers\Members";
                $route->id = 4;
                break;
            case "restricted":
                $route->controller = "Lucinda\Project\Controllers\Restricted";
                $route->id = 5;
                break;
        }
        // set view
        if (!$this->features->isREST) {
            switch ($url) {
                case "index":
                    $route->view = "index";
                    break;
                case "login":
                    $route->view = "login";
                    break;
                case "logout":
                    break;
                case "members":
                    $route->view = "members";
                    break;
                case "restricted":
                    $route->view = "restricted";
                    break;
            }
        }
        // set roles
        if ($this->features->security) {
            switch ($url) {
                case "index":
                    $route->roles = ($this->features->security->isCMS?"MEMBERS,ADMINISTRATORS":"GUESTS,MEMBERS");
                    break;
                case "login":
                    $route->roles = "GUESTS";
                    break;
                case "logout":
                case "members":
                    $route->roles = "MEMBERS,ADMINISTRATORS";
                    break;
                case "restricted":
                    $route->roles = "ADMINISTRATORS";
                    break;
            }
        }
        if ($this->features->headers) {
            if ($this->features->headers->caching) {
                switch ($url) {
                    case "index":
                        $route->no_cache = 0;
                        $route->cache_expiration = 10;
                        break;
                    case "login":
                    case "logout":
                        $route->no_cache = 1;
                        break;
                }
            }
            if ($this->features->headers->cors) {
                switch ($url) {
                    case "login":
                        $route->allowed_methods = "GET,POST";
                        break;
                    case "index":
                    case "logout":
                    case "members":
                    case "restricted":
                        $route->allowed_methods = "GET";
                        break;
                }
            }
        }
        $this->routes->routes[] = $route;
    }
    
    /**
     * Gets all routes added
     *
     * @return Routes
     */
    public function getResults(): Routes
    {
        return $this->routes;
    }
}

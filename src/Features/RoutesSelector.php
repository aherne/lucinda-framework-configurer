<?php

namespace Lucinda\Configurer\Features;

use Lucinda\Configurer\Features\Routes\Route;

/**
 * Sets up routes field of Features based on data already prompted by user
 */
class RoutesSelector
{
    public const CACHE_EXPIRATION = 10;
    private ?Routes $routes = null;
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
            $this->routes->default_roles = $features->security->isCMS ? "MEMBERS,ADMINISTRATORS" : "GUESTS,MEMBERS";
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
        $route->id = $this->getId($url);
        $route->controller = $this->getController($url);
        if (!$this->features->isREST) {
            $route->view = $this->getView($url);
        }
        if ($this->features->security) {
            $route->roles = $this->getRoles($url);
        }
        // set roles
        if ($this->features->headers) {
            if ($this->features->headers->caching) {
                $route->no_cache = $this->getNoCache($url);
                if ($url == "index") {
                    $route->cache_expiration = self::CACHE_EXPIRATION;
                }
            }
            if ($this->features->headers->cors) {
                $route->allowed_methods = $this->getAllowedMethods($url);
            }
        }
        $this->routes->routes[] = $route;
    }

    /**
     * Gets unique identifier matching route
     *
     * @param  string $url
     * @return int
     */
    private function getId(string $url): int
    {
        return match ($url) {
            "index"=>1,
            "login"=>2,
            "logout"=>3,
            "members"=>4,
            "restricted"=>5
        };
    }

    /**
     * Gets controller matching route
     *
     * @param  string $url
     * @return string|null
     */
    private function getController(string $url): ?string
    {
        return match ($url) {
            "index"=>"Lucinda\Project\Controllers\Index",
            "login"=>"Lucinda\Project\Controllers\Login",
            "logout"=>null,
            "members"=>"Lucinda\Project\Controllers\Members",
            "restricted"=>"Lucinda\Project\Controllers\Restricted"
        };
    }

    /**
     * Gets view matching route
     *
     * @param  string $url
     * @return string|null
     */
    private function getView(string $url): ?string
    {
        return match ($url) {
            "index"=>"index",
            "login"=>"login",
            "logout"=>null,
            "members"=>"members",
            "restricted"=>"restricted"
        };
    }

    /**
     * Gets access control roles matching route (separated by comma)
     *
     * @param  string $url
     * @return string
     */
    private function getRoles(string $url): string
    {
        return match ($url) {
            "index"=>($this->features->security->isCMS ? "MEMBERS,ADMINISTRATORS" : "GUESTS,MEMBERS"),
            "login"=>"GUESTS",
            "logout", "members" =>"MEMBERS,ADMINISTRATORS",
            "restricted"=>"ADMINISTRATORS"
        };
    }

    /**
     * Gets CORS Access-Control-Allow-Methods header value matching route
     *
     * @param  string $url
     * @return string
     */
    private function getAllowedMethods(string $url): string
    {
        return match ($url) {
            "index", "logout", "members", "restricted"=>"GET",
            "login"=>"GET,POST"
        };
    }

    /**
     * Gets whether caching should be prevented based on route
     *
     * @param  string $url
     * @return bool
     */
    private function getNoCache(string $url): bool
    {
        return match ($url) {
            "login", "logout" => true,
            default => false,
        };
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

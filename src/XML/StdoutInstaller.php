<?php

namespace Lucinda\Configurer\XML;

use Lucinda\WebSecurity\Token\SaltGenerator;

/**
 * Populates stdout.xml based on features selected by user
 */
class StdoutInstaller extends Installer
{
    public const DEFAULT_ROUTE = "index";
    public const SALT_LENGTH=128;

    /**
     * {@inheritDoc}
     *
     * @see Installer::generateXML()
     */
    protected function generateXML(): void
    {
        $this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><!DOCTYPE xml><xml></xml>');
        $this->setApplicationTag();
        $this->setResolversTag();
        $this->setTemplatingTag();
        $this->setLoggersTag();
        $this->setInternationalizationTag();
        $this->setHeadersTag();
        $this->setSqlTag();
        $this->setNoSqlTag();
        $this->setSecurityTag();
        $this->setRoutesTag();
        $this->setUsersTag();
        $this->setSessionTag();
    }

    /**
     * Populates <application> tag @ stdout.xml
     */
    private function setApplicationTag(): void
    {
        $application = $this->xml->addChild("application");
        $application->addAttribute("version", self::DEFAULT_VERSION);
        $application->addAttribute("default_format", (!$this->features->isREST ? "html" : "json"));
        $application->addAttribute("default_route", "index");
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
     * Populates <loggers> tag @ stdout.xml
     */
    private function setLoggersTag(): void
    {
        if (!$this->features->logging) {
            return;
        }

        $loggers = $this->xml->addChild("loggers");

        $logger = $loggers->addChild(self::DEFAULT_ENVIRONMENT)->addChild("logger");
        $logger->addAttribute("class", "Lucinda\Project\Loggers\File");
        $logger->addAttribute("path", "messages");
        $logger->addAttribute("format", "%d %f %l %m");
    }

    /**
     * Populates <internationalization> tag @ stdout.xml
     */
    private function setInternationalizationTag(): void
    {
        if (!$this->features->internationalization) {
            return;
        }

        $detectionMethod = "";
        switch ($this->features->internationalization->detectionMethod) {
        case "0":
            $detectionMethod = "header";
            break;
        case "1":
            $detectionMethod = "request";
            break;
        case "2":
            $detectionMethod = "session";
            break;
        }

        $internationalization = $this->xml->addChild("internationalization");
        $internationalization->addAttribute("locale", $this->features->internationalization->defaultLocale);
        $internationalization->addAttribute("method", $detectionMethod);
        $internationalization->addAttribute("folder", "locale");
        $internationalization->addAttribute("domain", "messages");
    }

    /**
     * Populates <http_caching> tag @ stdout.xml
     */
    private function setHeadersTag(): void
    {
        if (!$this->features->headers) {
            return;
        }

        $headers = $this->xml->addChild("headers");
        if ($this->features->headers->caching) {
            $headers->addAttribute(
                "no_cache",
                (string) (int) $this->features->headers->caching->no_cache
            );
            $headers->addAttribute(
                "expiration",
                (string) $this->features->headers->caching->expiration
            );
            $headers->addAttribute(
                "cacheable",
                "Lucinda\Project\Cacheables\Etag"
            );
        }
        if ($this->features->headers->cors) {
            $headers->addAttribute(
                "allow_credentials",
                (string) (int) $this->features->headers->cors->allow_credentials
            );
            $headers->addAttribute(
                "cors_max_age",
                (string) $this->features->headers->cors->max_age
            );
            $headers->addAttribute(
                "allowed_request_headers",
                $this->features->headers->cors->allowed_request_headers
            );
            $headers->addAttribute(
                "allowed_response_headers",
                $this->features->headers->cors->allowed_response_headers
            );
        }
    }

    /**
     * Populates <sql> tag @ stdout.xml
     */
    private function setSqlTag(): void
    {
        if (!$this->features->sqlServer) {
            return;
        }

        $driver = match ($this->features->sqlServer->driver) {
            0 => "mysql"
        };

        $server = $this->xml
            ->addChild("sql")
            ->addChild(self::DEFAULT_ENVIRONMENT)
            ->addChild("server");
        $server->addAttribute("driver", $driver);
        $server->addAttribute("host", $this->features->sqlServer->host);
        if ($this->features->sqlServer->port) {
            $server->addAttribute("port", (string) $this->features->sqlServer->port);
        }
        $server->addAttribute("username", $this->features->sqlServer->user);
        $server->addAttribute("password", $this->features->sqlServer->password);
        $server->addAttribute("schema", $this->features->sqlServer->schema);
        $server->addAttribute("charset", "UTF8");
    }

    /**
     * Populates <nosql> tag @ stdout.xml
     */
    private function setNoSqlTag(): void
    {
        if (!$this->features->nosqlServer) {
            return;
        }

        $driver = match ($this->features->nosqlServer->driver) {
            0 => "redis",
            1 => "memcache",
            2 => "memcached",
            3 => "couchbase",
            4 => "apc",
            5 => "apcu"
        };

        $server = $this->xml
            ->addChild("nosql")
            ->addChild(self::DEFAULT_ENVIRONMENT)
            ->addChild("server");
        $server->addAttribute("driver", $driver);
        if (!in_array($driver, ["apc","apcu"])) {
            $server->addAttribute("host", $this->features->nosqlServer->host);
            if ($this->features->nosqlServer->port) {
                $server->addAttribute("port", (string) $this->features->nosqlServer->port);
            }
            if ($driver == "couchbase") {
                $server->addAttribute("username", $this->features->nosqlServer->user);
                $server->addAttribute("password", $this->features->nosqlServer->password);
                $server->addAttribute("bucket_name", $this->features->nosqlServer->bucket);
                if ($this->features->nosqlServer->bucket_password) {
                    $server->addAttribute("bucket_password", $this->features->nosqlServer->bucket_password);
                }
            }
        }
    }

    /**
     * Populates <security> tag @ stdout.xml
     */
    private function setSecurityTag(): void
    {
        if (!$this->features->security) {
            return;
        }

        $security = $this->xml->addChild("security");

        $csrf = $security->addChild("csrf");
        $csrf->addAttribute("secret", $this->generateSecret());

        $persistenceDrivers = $security->addChild("persistence");
        switch ($this->features->security->persistenceDrivers) {
        case 0:
            $persistenceDrivers->addChild("session");
            $rememberMe = $persistenceDrivers->addChild("remember_me");
            $rememberMe->addAttribute("secret", $this->generateSecret());
            break;
        case 1:
            $synchronizerToken =$persistenceDrivers->addChild("synchronizer_token");
            $synchronizerToken->addAttribute("secret", $this->generateSecret());
            break;
        }

        $authentication = $security->addChild("authentication");
        $form = $authentication->addChild("form");
        if ($this->features->nosqlServer) {
            $form->addAttribute("throttler", "Lucinda\Project\DAO\NoSQLLoginThrottler");
        } elseif ($this->features->sqlServer) {
            $form->addAttribute("throttler", "Lucinda\Project\DAO\SQLLoginThrottler");
        }
        switch ($this->features->security->authenticationMethod) {
        case 0:
            $form->addAttribute("dao", "Lucinda\Project\DAO\UsersFormAuthentication");
            break;
        case 1:
            $form->addAttribute("dao", "Lucinda\Project\DAO\UsersFormAuthentication");
            $oauth2 = $authentication->addChild("oauth2");
            $oauth2->addAttribute("dao", "Lucinda\Project\DAO\UsersOAuth2Authentication");
            break;
        }

        $authorization = $security->addChild("authorization");
        switch ($this->features->security->authorizationMethod) {
        case 0:
            $dao = $authorization->addChild("by_dao");
            $dao->addAttribute("page_dao", "Lucinda\Project\DAO\PagesAuthorization");
            $dao->addAttribute("user_dao", "Lucinda\Project\DAO\UsersAuthorization");
            break;
        case 1:
            $authorization->addChild("by_route");
            break;
        }
    }

    /**
     * Populates <routes> tag @ stdout.xml
     */
    private function setRoutesTag(): void
    {
        $routes = $this->xml->addChild("routes");
        if ($this->features->routes->default_roles) {
            $routes->addAttribute("roles", $this->features->routes->default_roles);
        }
        foreach ($this->features->routes->routes as $info) {
            $route = $routes->addChild("route");
            $route->addAttribute("id", $info->url);
            if ($info->controller!==null) {
                $route->addAttribute("controller", $info->controller);
            }
            if ($info->view!==null) {
                $route->addAttribute("view", $info->view);
            }
            if ($info->roles!==null) {
                $route->addAttribute("roles", $info->roles);
            }
            if ($info->no_cache!==null) {
                $route->addAttribute("no_cache", (string) (int) $info->no_cache);
            }
            if ($info->cache_expiration!==null) {
                $route->addAttribute("cache_expiration", (string) $info->cache_expiration);
            }
            if ($info->allowed_methods!==null) {
                $route->addAttribute("allowed_methods", $info->allowed_methods);
            }
        }
    }


    /**
     * Populates <users> tag @ stdout.xml
     */
    private function setUsersTag(): void
    {
        if (!$this->features->users
            || !$this->features->security
            || $this->features->security->authenticationMethod!=2
        ) {
            return;
        }

        $users = $this->xml->addChild("users");
        $users->addAttribute("roles", $this->features->users->default_roles);
        foreach ($this->features->users->users as $info) {
            $user = $users->addChild("user");
            $user->addAttribute("id", (string) $info->id);
            $user->addAttribute("name", $info->name);
            $user->addAttribute("email", $info->email);
            $user->addAttribute("username", $info->username);
            $user->addAttribute("password", $info->password);
            $user->addAttribute("roles", $info->roles);
        }
    }

    /**
     * Populates <session> tag @ stdout.xml
     */
    private function setSessionTag(): void
    {
        if (!$this->features->isREST && (($this->features->internationalization && $this->features->internationalization->detectionMethod==2)
            || ($this->features->security && $this->features->security->persistenceDrivers==0))
        ) {
            $session = $this->xml->addChild("session");
            $session->addAttribute("auto_start", "1");
            if ($this->features->isLoadBalanced) {
                if ($this->features->sqlServer) {
                    $session->addAttribute("handler", "Lucinda\Project\DAO\SQLSessionHandler");
                } elseif ($this->features->nosqlServer) {
                    $session->addAttribute("handler", "Lucinda\Project\DAO\NoSQLSessionHandler");
                }
            }
        }
    }

    /**
     * Generates cryptographically secure key
     *
     * @return string
     */
    private function generateSecret(): string
    {
        $saltGenerator = new SaltGenerator(self::SALT_LENGTH);
        return $saltGenerator->getSalt();
    }
}

<?php
namespace Lucinda\Configurer\SQL;

use Lucinda\Configurer\Features\Features;

/**
 * Creates and populates SQL tables based on features selected by user
 */
class Installer
{
    const ROLES = ["MEMBERS"=>1, "ADMINISTRATORS"=>2];
    
    private Features $features;
    private \PDO $pdo;
    
    /**
     * SQLInstallation constructor.
     * @param Features $features
     * @throws \Exception If installation fails.
     */
    public function __construct(Features $features)
    {
        $this->features = $features;

        $driver = match($this->features->sqlServer->driver) {
            0 => "mysql"
        };
        $pdo = new \PDO($driver.":dbname=".$this->features->sqlServer->schema.";host=".$this->features->sqlServer->host, $this->features->sqlServer->user, $this->features->sqlServer->password);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
        
        if ($this->features->migrations && $this->features->migrations->storageMethod==0) {
            $this->setMigrationsTable();
        }
        
        if ($this->features->isLoadBalanced && $this->features->sqlServer) {
            $this->setSessionsTable();
        }
        
        if (!$this->features->security || ($this->features->security->authenticationMethod==2 && $this->features->security->authorizationMethod==1)) {
            return;
        }
        
        $this->cleanUp();
        
        if (!$features->nosqlServer) {
            $this->setThrottlerTable();
        }
        
        if ($this->features->security->authenticationMethod == 1) {
            $this->setOauth2ProvidersTable();
            $this->setUsersTable2();
            $this->setUsersOauth2Table();
            $this->setUsersFormTable();
        } elseif ($this->features->security->authenticationMethod == 0) {
            $this->setUsersTable1();
        }
        
        if ($this->features->security->authorizationMethod == 0) {
            $this->setResourcesTable();
            if ($this->features->security->authenticationMethod == 2) {
                $this->setUsersTable3();
            }
            if ($this->features->security->isCMS) {
                $this->setRolesTable();
                $this->setUsersRolesTable();
                $this->setRolesResourcesTable();
            } else {
                $this->setUsersResourcesTable();
            }
        }
    }

    /**
     * Drops tables to be created later
     */
    private function cleanUp()
    {
        $tables = array("users__form", "users__oauth2", "oauth2_providers", "users_resources", "roles_resources", "users_roles", "roles", "resources", "users", "user_logins");
        foreach ($tables as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS ".$table);
        }
    }

    /**
     * Creates and fills table: users (used to identify users)
     */
    private function setUsersTable1(): void
    {
        $this->pdo->exec("
            CREATE TABLE users (
            	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            	username VARCHAR(255) NOT NULL,
            	password VARCHAR(255) NOT NULL,
            	name VARCHAR(255) NOT NULL,
            	email VARCHAR(255) NOT NULL,
                date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            	PRIMARY KEY(id),
            	UNIQUE(username),
                UNIQUE(email)
            ) Engine=INNODB
            ");
        foreach ($this->features->users->users as $user) {
            $this->pdo->exec("
                INSERT INTO users (id, username, password, name, email) VALUES
                (".$user->id.", '".$user->username."', '".$user->password."', '".$user->name."', '".$user->email."')
                ");
        }
    }
    
    private function setUsersTable2(): void
    {
        $this->pdo->exec("
            CREATE TABLE users
            (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id),
            UNIQUE(email)
            ) Engine=INNODB
            ");
        foreach ($this->features->users->users as $user) {
            $this->pdo->exec("
                INSERT INTO users (id, name, email) VALUES
                (".$user->id.", '".$user->name."', '".$user->email."')
                ");
        }
    }
    
    private function setUsersTable3(): void
    {
        $this->pdo->exec("
            CREATE TABLE users
            (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            date_created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id),
            UNIQUE(email)
            ) Engine=INNODB
            ");
        foreach ($this->features->users->users as $user) {
            $this->pdo->exec("
                INSERT INTO users (id, email) VALUES
                (".$user->id.", '".$user->email."')
                ");
        }
    }
    
    /**
     * Sets table that stores login attempts and penalties
     */
    private function setThrottlerTable(): void
    {
        $this->pdo->exec("
             CREATE TABLE user_logins (
             id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
             ip VARCHAR(45) NOT NULL,
             username VARCHAR(255) NOT NULL,
             attempts BIGINT UNSIGNED NOT NULL default 0,
             penalty_expiration DATETIME DEFAULT NULL,
             date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
             PRIMARY KEY(id),
             UNIQUE(ip, username)
             ) Engine=InnoDB;
            ");
    }
    
    /**
     * Sets table that stores migrations
     */
    private function setMigrationsTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS migrations
            (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            class_name VARCHAR(255) NOT NULL,
            is_successful BOOLEAN NOT NULL DEFAULT TRUE,
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id),
            UNIQUE(class_name)
            ) Engine=INNODB
            ");
    }
    
    /**
     * Sets table that stores sessions
     */
    private function setSessionsTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS sessions
            (
            id VARCHAR(50) NOT NULL,
            value BLOB NOT NULL,
            date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            expires INT UNSIGNED NOT NULL DEFAULT 0,
            PRIMARY KEY(id),
            UNIQUE(class_name)
            ) Engine=INNODB
            ");
    }

    /**
     * Creates and fills table: resources (used to identify resources to apply authorization later on)
     */
    private function setResourcesTable(): void
    {
        $this->pdo->exec("
            CREATE TABLE resources
            (
            id smallint unsigned not null auto_increment,
            url varchar(255) not null,
            is_public boolean not null default false,
            PRIMARY KEY(id),
            unique(url)
            ) Engine=INNODB
            ");
        foreach ($this->features->routes->routes as $route) {
            $this->pdo->exec("
            INSERT INTO resources (id, url, is_public) VALUES
            (".$route->id.", '".$route->url."', ".(str_contains($route->roles, "GUESTS") ?1:0).")");
        }
    }

    /**
     * Creates and fills table: roles (use to identify resource access levels)
     */
    public function setRolesTable(): void
    {
        $this->pdo->exec("
        CREATE TABLE roles
        (
        id tinyint unsigned not null auto_increment,
        name varchar(255) not null,
        PRIMARY KEY(id),
        unique(name)
        ) Engine=INNODB
        ");
        foreach (self::ROLES as $name=>$id) {
            $this->pdo->exec("
            INSERT INTO roles (id, name) VALUES
            (".$id.", '".$name."')
            ");
        }
    }

    /**
     * Creates and fills table: users_roles (used to identify roles users belong to)
     */
    public function setUsersRolesTable(): void
    {
        $this->pdo->exec("
        CREATE TABLE users_roles
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        role_id tinyint unsigned not null,
        PRIMARY KEY(id),
        foreign key(user_id) references users(id) on delete cascade,
        foreign key(role_id) references roles(id) on delete cascade
        ) Engine=INNODB
        ");
        foreach ($this->features->users->users as $user) {
            $userRoles = explode(",", $user->roles);
            foreach ($userRoles as $role) {
                $this->pdo->exec("
                INSERT INTO users_roles (user_id, role_id) VALUES
                (".$user->id.", ".self::ROLES[$role].")
                ");
            }
        }
    }

    /**
     * Creates and fills table: roles_resources (used for role authorization to requested resource)
     */
    public function setRolesResourcesTable(): void
    {
        $this->pdo->exec("
        CREATE TABLE roles_resources
        (
        id int unsigned not null auto_increment,
        role_id tinyint unsigned not null,
        resource_id smallint unsigned not null,
        PRIMARY KEY(id),
        foreign key(role_id) references roles(id) on delete cascade,
        foreign key(resource_id) references resources(id) on delete cascade
        ) Engine=INNODB
        ");
        foreach ($this->features->routes->routes as $route) {
            $routeRoles = explode(",", $route->roles);
            foreach ($routeRoles as $role) {
                if (isset(self::ROLES[$role])) {
                    $this->pdo->exec("
                    INSERT INTO roles_resources (resource_id, role_id) VALUES
                    (".$route->id.", ".self::ROLES[$role].")
                    ");
                }
            }
        }
    }

    /**
     * Creates and fills table: users_resources (used for user authorization to requested resource)
     */
    public function setUsersResourcesTable(): void
    {
        $this->pdo->exec("
        CREATE TABLE users_resources
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        resource_id smallint unsigned not null,
        PRIMARY KEY(id),
        foreign key(user_id) references users(id) on delete cascade,
        foreign key(resource_id) references resources(id) on delete cascade
        ) Engine=INNODB
        ");
        $userRoles = [];
        foreach ($this->features->users->users as $user) {
            $userRoles[$user->id] = explode(",", $user->roles);
        }
        $resourceRoles = [];
        foreach ($this->features->routes->route as $route) {
            $resourceRoles[$route->id] = explode(",", $route->roles);
        }
        foreach ($userRoles as $userID=>$roles1) {
            foreach ($resourceRoles as $resourceID=>$roles2) {
                foreach ($roles1 as $role) {
                    if (in_array($role, $roles2)) {
                        $this->pdo->exec("
                        INSERT INTO users_resources (resource_id, user_id) VALUES
                        (".$resourceID.", ".$userID.")
                        ");
                    }
                }
            }
        }
    }

    /**
     * Creates and fills table: oauth2_providers (used to identify oauth2 providers supported by framework)
     */
    public function setOauth2ProvidersTable(): void
    {
        $this->pdo->exec("
        CREATE TABLE oauth2_providers
        (
        id tinyint unsigned not null auto_increment,
        name varchar(255) not null,
        PRIMARY KEY(id),
        unique(name)
        ) Engine=INNODB
        ");
        
        $this->pdo->exec("
        INSERT INTO oauth2_providers (id, name) VALUES
        (1, 'Facebook'),
        (2, 'Google'),
        (3, 'GitHub'),
        (4, 'Instagram'),
        (5, 'LinkedIn'),
        (6, 'VK'),
        (7, 'Yahoo'),
        (8, 'Yandex')
        ");
    }

    /**
     * Creates and fills table: users_oauth2 (used for oauth2 authentication)
     */
    public function setUsersOauth2Table(): void
    {
        $this->pdo->exec("
        CREATE TABLE users__oauth2
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        remote_user_id varchar(32) not null,
        driver_id tinyint unsigned not null,
        access_token varchar(255) not null,
        PRIMARY KEY(id),
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(remote_user_id, driver_id)
        ) Engine=INNODB
        ");
    }

    /**
     * Creates and fills table: users_form (used for form authentication)
     */
    public function setUsersFormTable(): void
    {
        $this->pdo->exec("
        CREATE TABLE users__form
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        username varchar(255) not null,
        password char(60) not null,
        PRIMARY KEY(id),
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE(username),
        UNIQUE(user_id),
        KEY(username, password)
        ) Engine=INNODB
        ");
        foreach ($this->features->users->users as $user) {
            $this->pdo->exec("
                INSERT INTO users__form (user_id, username, password) VALUES
                (".$user->id.", '".$user->username."', '".$user->password."')
                ");
        }
    }
}

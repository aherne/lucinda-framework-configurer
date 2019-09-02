<?php
namespace Lucinda\Configurer;

/**
 * Creates and populates SQL tables based on features selected by user
 */
class SQLInstallation
{
    private $features;
    /**
     * @var PDO
     */
    private $pdo;
    // TODO: create users table instead of users__form is oauth2 is not used
    /**
     * SQLInstallation constructor.
     * @param Features $features
     * @throws Exception If installation fails.
     */
    public function __construct($features)
    {
        if (!$features->security || (in_array("access control list", $features->security->authenticationMethods) && $this->features->security->authorizationMethod=="access control list")) {
            return;
        }
        
        $this->features = $features;
        $this->setDriver();
        $this->cleanUp();
        
        $this->setUsersTable();
        
        if (in_array("oauth2 providers", $this->features->security->authenticationMethods)) {
            $this->setOauth2ProvidersTable();
            $this->setUsersOauth2Table();
            if (sizeof($this->features->security->authenticationMethods) == 2) {
                $this->setUsersFormTable();
            }
        }
        
        if ($this->features->security->authorizationMethod == "database") {
            $this->setResourcesTable();
            if ($this->features->siteType == "CMS") {
                $this->setRolesTable();
                $this->setUsersRolesTable();
                $this->setRolesResourcesTable();
            } else {
                $this->setUsersResourcesTable();
            }
        }
    }

    /**
     * Creates and saves PDO instance based on features chosen by user.
     */
    private function setDriver()
    {
        $pdo = new PDO($this->features->sqlServer->driver.":dbname=".$this->features->sqlServer->schema.";host=".$this->features->sqlServer->host, $this->features->sqlServer->user, $this->features->sqlServer->password);
        $statement = $pdo->query("SHOW GRANTS");
        $found = false;
        while ($value = $statement->fetch(PDO::FETCH_COLUMN)) {
            if (!(strpos($value, "ALL PRIVILEGES") || (strpos($value, "CREATE") && strpos($value, "DROP")))) {
                $found = true;
            }
        }
        if (!$found) {
            throw new \Exception("ERROR: User '".$this->features->sqlServer->user."' must have CREATE and DROP rights on '".$this->features->sqlServer->schema."' for tables to be installed!");
        }
        $this->pdo = $pdo;
    }

    /**
     * Drops tables to be created later
     */
    private function cleanUp()
    {
        $tables = array("users_form", "users_oauth2", "oauth2_providers", "users_resources", "roles_resources", "users_roles", "roles", "resources", "users");
        foreach ($tables as $table) {
            $this->pdo->exec("DROP TABLE IF EXISTS ".$table);
        }
    }

    /**
     * Creates and fills table: users (used to identify users)
     */
    private function setUsersTable()
    {
        if (sizeof($this->features->security->authenticationMethods)==1 && $this->features->security->authenticationMethods[0] = "database") {
            $this->pdo->exec("
            CREATE TABLE users (
            	id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            	username VARCHAR(255) NOT NULL,
            	password VARCHAR(255) NOT NULL,
            	name VARCHAR(255) NOT NULL,
            	email VARCHAR(255) NOT NULL,
            	PRIMARY KEY(id),
            	UNIQUE(username),
                UNIQUE(email)
            ) Engine=INNODB
            ");
            $this->pdo->exec("
        INSERT INTO users (id, username, password, name, email) VALUES
        (1, 'john', '".password_hash("doe", PASSWORD_BCRYPT)."', 'John Doe', 'john@doe.com'),
        (2, 'jane', '".password_hash("doe", PASSWORD_BCRYPT)."', 'Jane Doe', 'jane@doe.com')
        ");
        } else {
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
            $this->pdo->exec("
        INSERT INTO users (id, name, email) VALUES
        (1, 'John Doe', 'john@doe.com'),
        (2, 'Jane Doe', 'jane@doe.com')
        ");
        }
    }


    /**
     * Creates and fills table: resources (used to identify resources to apply authorization later on)
     */
    private function setResourcesTable()
    {
        $this->pdo->exec("
        CREATE TABLE resources
        (
        id smallint unsigned not null auto_increment,
        url varchar(255) not null,
        is_public boolean not null default false,
        primary key(id),
        unique(url)
        ) Engine=INNODB
        ");
        $routesPossible=array(
            "index"=>  ($this->features->siteType=="CMS"?0:1),
            "login"=>1,
            "logout"=>0,
            "members"=>0
        );
        if ($this->features->siteType == "CMS") {
            $routesPossible["restricted"] = 0;
        }
        foreach ($this->features->security->oauth2Providers as $oauth2DriverInfo) {
            $routesPossible["login/".strtolower($oauth2DriverInfo->driver)] = 1;
        }
        foreach ($routesPossible as $route=>$isPublic) {
            $this->pdo->exec("
            INSERT INTO resources (url, is_public) VALUES
            ('".$route."', ".$isPublic.")");
        }
    }

    /**
     * Creates and fills table: roles (use to identify resource access levels)
     */
    public function setRolesTable()
    {
        $this->pdo->exec("
        CREATE TABLE roles
        (
        id tinyint unsigned not null auto_increment,
        name varchar(255) not null,
        primary key(id),
        unique(name)
        ) Engine=INNODB
        ");
        $this->pdo->exec("
        INSERT INTO roles (id, name) VALUES
        (1, 'MEMBER'),
        (2, 'ADMINISTRATOR')
        ");
    }

    /**
     * Creates and fills table: users_roles (used to identify roles users belong to)
     */
    public function setUsersRolesTable()
    {
        $this->pdo->exec("
        CREATE TABLE users_roles
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        role_id tinyint unsigned not null,
        primary key(id),
        foreign key(user_id) references users(id) on delete cascade,
        foreign key(role_id) references roles(id) on delete cascade
        ) Engine=INNODB
        ");
        $this->pdo->exec("
        INSERT INTO users_roles (user_id, role_id) VALUES
        (1, 1),
        (2, 1),
        (2, 2)
        ");
    }

    /**
     * Creates and fills table: roles_resources (used for role authorization to requested resource)
     */
    public function setRolesResourcesTable()
    {
        $this->pdo->exec("
        CREATE TABLE roles_resources
        (
        id int unsigned not null auto_increment,
        role_id tinyint unsigned not null,
        resource_id smallint unsigned not null,
        primary key(id),
        foreign key(role_id) references roles(id) on delete cascade,
        foreign key(resource_id) references resources(id) on delete cascade
        ) Engine=INNODB
        ");
        $rights = array(
            1=>array(1,2),
            3=>array(1,2),
            4=>array(1,2),
            5=>array(2)
        );
        foreach ($rights as $resourceID=>$roles) {
            foreach ($roles as $roleID) {
                $this->pdo->exec("
                INSERT INTO roles_resources (role_id, resource_id) VALUES
                ('".$roleID."', ".$resourceID.")");
            }
        }
    }

    /**
     * Creates and fills table: users_resources (used for user authorization to requested resource)
     */
    public function setUsersResourcesTable()
    {
        $this->pdo->exec("
        CREATE TABLE users_resources
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        resource_id smallint unsigned not null,
        primary key(id),
        foreign key(user_id) references users(id) on delete cascade,
        foreign key(resource_id) references resources(id) on delete cascade
        ) Engine=INNODB
        ");
        $rights = array(
            3=>array(1,2),
            4=>array(1,2)
        );
        foreach ($rights as $resourceID=>$users) {
            foreach ($users as $userID) {
                $this->pdo->exec("
                INSERT INTO users_resources (user_id, resource_id) VALUES
                ('".$userID."', ".$resourceID.")");
            }
        }
    }

    /**
     * Creates and fills table: oauth2_providers (used to identify oauth2 providers supported by framework)
     */
    public function setOauth2ProvidersTable()
    {
        $this->pdo->exec("
        CREATE TABLE oauth2_providers
        (
        id tinyint unsigned not null auto_increment,
        name varchar(255) not null,
        primary key(id),
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
        (7, 'Yandex')
        ");
    }

    /**
     * Creates and fills table: users_oauth2 (used for oauth2 authentication)
     */
    public function setUsersOauth2Table()
    {
        $this->pdo->exec("
        CREATE TABLE users__oauth2
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        remote_user_id bigint unsigned not null,
        driver_id tinyint unsigned not null,
        access_token varchar(255) not null,
        primary key(id),
        foreign key(user_id) references users(id) on delete cascade,
        unique(remote_user_id, driver_id)
        ) Engine=INNODB
        ");
    }

    /**
     * Creates and fills table: users_form (used for form authentication)
     */
    public function setUsersFormTable()
    {
        $this->pdo->exec("
        CREATE TABLE users__form
        (
        id int unsigned not null auto_increment,
        user_id int unsigned not null,
        username varchar(255) not null,
        password char(60) not null,
        primary key(id),
        foreign key(user_id) references users(id) on delete cascade,
        unique(username),
        key(username, password)
        ) Engine=INNODB
        ");
        $this->pdo->exec("
        INSERT INTO users__form (user_id, username, password) VALUES
        (1, 'john', '".password_hash("doe", PASSWORD_BCRYPT)."'),
        (2, 'jane', '".password_hash("doe", PASSWORD_BCRYPT)."')");
    }
}

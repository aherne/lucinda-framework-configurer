<?php
namespace Lucinda\Configurer\Features;

/**
 * Wrapper of Features object, able to interpret its fields into a console installer
 */
class FeaturesSelector
{    
    private $features;
    
    /**
     * Begins selector
     */
    public function __construct()
    {
        $this->features = new \Lucinda\Configurer\Features\Features();
        $i=0;
        $this->run($i, '\Lucinda\Configurer\Features\Features', $this->features);
    }
    
    /**
     * Instances and reads class fields documentation and sets up a prompter for users to set them
     * 
     * @param int $i
     * @param string $className
     * @param object $object
     */
    private function run(int $i, string $className, $object): void
    {
        // compile properties
        $properties = [];
        $rc = new \ReflectionClass($className);
        foreach($rc->getProperties() as $property) {
            $properties[$property->getName()] = new DocBlockParser($property->getDocComment());
        }
        
        // create object
        $j = 0;
        foreach ($properties as $name=>$info) {
            $j++;
            if ($info->getType() == '\Lucinda\Configurer\Features\Routes') {
                $selector = new RoutesSelector($this->features);
                $object->$name = $selector->getRoutes();
            } else if ($info->getType() == '\Lucinda\Configurer\Features\Users') {
                $selector = new UsersSelector($this->features);
                $object->$name = $selector->getUsers();
            } else if ($info->getType() == '\Lucinda\Configurer\Features\Exceptions') {
                $selector = new ExceptionsSelector($this->features);
                $object->$name = $selector->getExceptions();
            }else {
                $result = $this->prompt($i, "[".$j."/".($i==0?sizeof($properties)-3:sizeof($properties))."]", $className, $name, $info);
                if (strpos($info->getType(), "\\")===0 && $result==="1") {
                    $childClassName = $info->getType();
                    $object->$name = new $childClassName();
                    $this->run($i+1, $childClassName, $object->$name);
                } else {
                    $object->$name = $result;
                }
            }            
        }
    }
    
    /**
     * Prompts user to set respective class field and runs regex validation on values prompted by user
     * 
     * @param int $k
     * @param string $index
     * @param string $className
     * @param string $parameterName
     * @param DocBlockParser $info
     * @return string
     */
    private function prompt(int $k, string $index, string $className, string $parameterName, DocBlockParser $info): string
    {
        $indent = str_repeat(" ", 5*$k);
        $defaultOption = $info->getDefaultOption();
        $message = "";
        if (!empty($info->getOptions())) {
            echo $indent.$index." ".$info->getMessage().":\n";
            $options = $info->getOptions();
            foreach ($options as $i=>$option) {
                echo $indent."     ".$i.". ".$option."\n";
            }
            $message = $indent."     Choose one of above".($defaultOption!==""?" or hit enter to confirm #".$defaultOption:($info->getOptional()?" or hit enter":"")).": ";
        } else {
            $message = $indent.$index." ".$info->getMessage().($defaultOption!==""?" or hit enter to confirm '".$defaultOption."'":($info->getOptional()?" or hit enter":"")).": ";
        }
        $result = \readline($message);
        if($result==="") {
            $result = $defaultOption;
        }
        if($info->getOptional() && !$result){
            return $result;
        }
        if(!$this->validateValue($className, $parameterName, $info->getValidator(), $result)) {
            echo $indent.(strpos(PHP_OS, "WIN") !== 0?"\e[1;31;31mERROR\e[0m":"ERROR").": value entered is invalid!\n";
            return $this->prompt($k, $index, $className, $parameterName, $info);
        }
        return $result;
    }
    
    /**
     * Performs automatic regex-based validation of value prompted by user based on @validator annotation, allowing also to fine grain validation for certain fields
     * 
     * @param string $className
     * @param string $parameterName
     * @param string $regex
     * @param string $result
     * @throws \Exception
     * @return bool
     */
    private function validateValue(string $className, string $parameterName, string $regex, string $result): bool
    {
        if($regex) {
            if($className=='\Lucinda\Configurer\Features\NoSQLServer') {
                switch($parameterName) {
                    case "host":
                    case "port":
                        if(in_array($this->features->nosqlServer->driver, ["APC", "APCu"])) {
                            return !$result?true:false;
                        }
                        break;
                    case "user":
                    case "bucket":
                    case "password":
                        if($this->features->nosqlServer->driver!="Couchbase") {
                            return !$result?true:false;
                        }
                        break;
                }
            }
            if($className=='\Lucinda\Configurer\Features\Security') {
                switch($parameterName) {
                    case "persistenceDrivers":
                        if ($result==="") {
                            return false;
                        }
                        if(in_array($result, [0,1])) {
                            return !$this->features->isREST;
                        } else {
                            return $this->features->isREST;
                        }
                        break;
                    case "authenticationMethod":
                        if ($result!=2 && !$this->features->sqlServer) {
                            throw new \Exception("Database authentication requires an SQL server");
                        }
                        break;
                    case "authorizationMethod":
                        if ($result==="") {
                            return false;
                        }
                        if ($result!=1 && !$this->features->sqlServer) {
                            throw new \Exception("Database authorization requires an SQL server");
                        }
                        if ($result==1 && $this->features->security->authenticationMethod==2 && !$this->features->nosqlServer) {
                            throw new \Exception("XML authentication and authorization requires a NoSQL server for throttling");
                        }
                        $isCMS = $this->features->security->isCMS;
                        if($result==0) {
                            return $isCMS;
                        } else {
                            return !$isCMS;
                        }
                        break;
                }
            }
            return preg_match("/^".$regex."$/", $result)>0;
        } else {
            throw new \Exception($className."#".$parameterName." misses validator");
        }
    }
    
    /**
     * Gets features set by user
     * 
     * @return \Lucinda\Configurer\Features\Features
     */
    public function getFeatures()
    {
        return $this->features;
    }
}

<?php
namespace Lucinda\Configurer;

use \Lucinda\Configurer\Features\Features;
use \Lucinda\Configurer\Features\DocBlockParser;

/**
 * Wrapper of Features object, able to interpret its fields into a console installer
 */
class FeaturesSelector
{
    private Features $features;
    
    /**
     * Begins selector
     */
    public function __construct()
    {
        $this->features = new Features();
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
    private function run(int $i, string $className, object $object): void
    {
        // compile properties
        $properties = [];
        $rc = new \ReflectionClass($className);
        foreach ($rc->getProperties() as $property) {
            $properties[$property->getName()] = new DocBlockParser($property->getDocComment());
        }
        
        // create object
        $j = 0;
        foreach ($properties as $name=>$info) {
            $j++;
            if ($handler = $info->getHandler()) {
                $selector = new $handler($this->features);
                $object->$name = $selector->getResults();
            } else {
                $condition = $info->getCondition();
                if ($condition && !$this->validateCondition($object, $condition)) {
                    continue;
                }
                if ($className=='\Lucinda\Configurer\Features\Security') {
                    if ($name=="persistenceDrivers") {
                        $object->$name = ($this->features->isREST?1:0);
                        continue;
                    }
                    if ($name=="authorizationMethod") {
                        $object->$name = ($this->features->security->isCMS?0:1);
                        continue;
                    }
                }
                $result = $this->prompt($i, "[".$j."/".($i==0?sizeof($properties)-3:sizeof($properties))."]", $className, $name, $info);
                if (str_starts_with($info->getType(), "\\") && $result==="1") {
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
     * @throws \Exception
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
            $message = $indent."     Choose one of above".($defaultOption!==""?" or hit enter to confirm #".$defaultOption:($info->getOptional()?" or hit enter for default":"")).": ";
        } else {
            $message = $indent.$index." ".$info->getMessage().($defaultOption!==""?" or hit enter to confirm '".$defaultOption."'":($info->getOptional()?" or hit enter for default":"")).": ";
        }
        $result = \readline($message);
        if ($result==="") {
            $result = $defaultOption;
        }
        if ($info->getOptional() && !$result) {
            return $result;
        }
        if (!$this->validateValue($className, $parameterName, $info->getValidator(), $result)) {
            echo $indent."ERROR: value entered is invalid!\n";
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
        if ($regex) {
            return preg_match("/^".$regex."$/", $result)>0;
        } else {
            throw new \Exception($className."#".$parameterName." misses validator");
        }
    }
    
    /**
     * Validates whether to display field prompt based on @if annotation
     *
     * @param object $object
     * @param array $condition
     * @return bool
     */
    private function validateCondition(object $object, array $condition): bool
    {
        foreach ($condition as $name=>$values) {
            if (in_array($object->$name, $values)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Gets features set by user
     *
     * @return Features
     */
    public function getFeatures(): Features
    {
        return $this->features;
    }
}

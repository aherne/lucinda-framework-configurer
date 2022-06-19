<?php

namespace Lucinda\Configurer;

use Lucinda\Configurer\Features\Features;
use Lucinda\Configurer\Features\DocBlockParser;

/**
 * Wrapper of Features object, able to interpret its fields into a console installer
 */
class FeaturesSelector
{
    private Features $features;

    /**
     * Begins selector
     *
     * @throws \Exception
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
     * @param  int    $indentFactor
     * @param  string $className
     * @param  object $object
     * @throws \Exception
     */
    private function run(int $indentFactor, string $className, object $object): void
    {
        $j = 0;
        $properties = $this->getClassFields($className);
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
                        $object->$name = ($this->features->isREST ? 1 : 0);
                        continue;
                    }
                    if ($name=="authorizationMethod") {
                        $object->$name = ($this->features->security->isCMS ? 0 : 1);
                        continue;
                    }
                }
                $index = "[".$j."/".($indentFactor==0 ? sizeof($properties)-3 : sizeof($properties))."]";
                $result = $this->prompt($indentFactor, $index, $className, $name, $info);
                if (str_starts_with($info->getType(), "\\")) {
                    if ($result=="1") {
                        $childClassName = $info->getType();
                        $object->$name = new $childClassName();
                        $this->run($indentFactor+1, $childClassName, $object->$name);
                    } else {
                        $object->$name = null;
                    }
                } else {
                    $object->$name = ($info->getType()=="integer" ? (int) $result : $result);
                }
            }
        }
    }

    /**
     * Gets encapsulated version of feature class fields
     *
     * @param  string $className
     * @return array<string,DocBlockParser>
     * @throws \ReflectionException
     */
    private function getClassFields(string $className): array
    {
        $properties = [];
        $reflectionClass = new \ReflectionClass($className);
        $fields = $reflectionClass->getProperties();
        foreach ($fields as $property) {
            $properties[$property->getName()] = new DocBlockParser($property->getDocComment());
        }
        return $properties;
    }

    /**
     * Prompts user to set respective class field and runs regex validation on values prompted by user
     *
     * @param  int            $indentFactor
     * @param  string         $index
     * @param  string         $className
     * @param  string         $parameterName
     * @param  DocBlockParser $info
     * @return string
     * @throws \Exception
     */
    private function prompt(
        int $indentFactor,
        string $index,
        string $className,
        string $parameterName,
        DocBlockParser $info
    ): string {
        $indent = str_repeat(" ", 5*$indentFactor);
        if (!empty($info->getOptions())) {
            echo $this->getMainMessage($index, $indent, $info);
        }
        $result = \readline($this->getInteractiveMessage($index, $indent, $info));
        if ($result==="") {
            $result = $info->getDefaultOption();
        }
        if ($info->hasOptional() && !$result) {
            return $result;
        }
        if (!$this->validateValue($className, $parameterName, $info->getValidator(), $result)) {
            echo $indent."ERROR: value entered is invalid!\n";
            return $this->prompt($indentFactor, $index, $className, $parameterName, $info);
        }
        return $result;
    }

    /**
     * Gets body of interactive message to display
     *
     * @param  string         $index
     * @param  string         $indent
     * @param  DocBlockParser $info
     * @return string
     */
    private function getInteractiveMessage(string $index, string $indent, DocBlockParser $info): string
    {
        $defaultOptionText =  "";
        if ($info->getDefaultOption() !== "") {
            $defaultOptionText = " or hit enter to confirm '".$info->getDefaultOption()."'";
        } elseif ($info->hasOptional()) {
            $defaultOptionText = " or hit enter for default";
        }
        $message = "";
        if (!empty($info->getOptions())) {
            $message = $indent."     Choose one of above".$defaultOptionText.": ";
        } else {
            $message = $indent.$index." ".$info->getMessage().$defaultOptionText.": ";
        }
        return $message;
    }

    /**
     * Gets body of options text associated to interactive message
     *
     * @param  string         $index
     * @param  string         $indent
     * @param  DocBlockParser $info
     * @return string
     */
    private function getMainMessage(string $index, string $indent, DocBlockParser $info): string
    {
        $message = $indent.$index." ".$info->getMessage().":\n";
        $options = $info->getOptions();
        foreach ($options as $i=>$option) {
            $message .= $indent."     ".$i.". ".$option."\n";
        }
        return $message;
    }

    /**
     * Performs automatic regex-based validation of value prompted by user based on @validator annotation, allowing
     * also to fine grain validation for certain fields
     *
     * @param  string $className
     * @param  string $parameterName
     * @param  string $regex
     * @param  string $result
     * @throws \Exception
     * @return bool
     */
    private function validateValue(
        string $className,
        string $parameterName,
        string $regex,
        $result
    ): bool {
        if ($regex) {
            return preg_match("/^".$regex."$/", $result)>0;
        } else {
            throw new \Exception($className."#".$parameterName." misses validator");
        }
    }

    /**
     * Validates whether to display field prompt based on @if annotation
     *
     * @param  object                 $object
     * @param  array<string,string[]> $condition
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

<?php
namespace Lucinda\Configurer;

/**
 * Encapsulates a console prompter
 */
class Prompter
{
    /**
     * Prompts an error message to screen
     *
     * @param string $message Contents of error message
     */
    public function error($message)
    {
        echo "ERROR: ".$message."\n";
    }

    /**
     * Prompts a text input to screen.
     *
     * @param string $message Message to display for input.
     * @param string $defaultValue Default text input value.
     * @param callable $validator Validator able to check if value is right.
     * @return string Value entered in text input.
     */
    public function text($message, $defaultValue=null, $validator = null)
    {
        $result = readline($message.($defaultValue!==null?" or hit enter to confirm '".$defaultValue."'":"").": ");
        if (!$result) {
            if ($defaultValue!==null) {
                $result = $defaultValue;
            } else {
                $this->error("Value cannot be empty!");
                return $this->text($message, $defaultValue, $validator);
            }
        }
        if (!$validator($result)) {
            $this->error("Value is invalid!");
            return $this->text($message, $defaultValue, $validator);
        }
        return $result;
    }

    /**
     * Prompts a multiple select to screen
     *
     * @param string $message Message to display for select.
     * @param string[] $availableOptions List of available options
     * @param null $defaultOption Value of default option.
     * @return string[] Gets options selected
     */
    public function multipleSelect($message, $availableOptions, $defaultOption=null)
    {
        echo $message.":\n";
        foreach ($availableOptions as $option) {
            echo "\t- ".$option."\n";
        }
        $result = readline("write any of above (comma separated if more than one)".($defaultOption!==null?" or hit enter to confirm '".$this->stripHint($availableOptions[$defaultOption])."'":"").": ");
        if (!$result) {
            if ($defaultOption!==null) {
                $result = $availableOptions[$defaultOption];
            } else {
                $this->error("Value cannot be empty!");
                return $this->multipleSelect($message, $defaultOption, $availableOptions);
            }
        }
        $tmp = explode(",", $result);
        $selectedOptions = array();
        foreach ($tmp as $option) {
            $option = trim($option);
            if (!in_array($option, $availableOptions)) {
                $this->error("Value '".$option."' is not supported!");
                return $this->multipleSelect($message, $defaultOption, $availableOptions);
            }
            $selectedOptions[] = $option;
        }
        return $selectedOptions;
    }

    /**
     * Prompts a single select to screen
     *
     * @param string $message Message to display for select.
     * @param string[] $availableOptions List of available options
     * @param null $defaultOption Value of default option.
     * @return string
     */
    public function singleSelect($message, $availableOptions, $defaultOption=null)
    {
        echo $message.":\n";
        $availableOptionsWithoutHints = array();
        foreach ($availableOptions as $option) {
            echo "\t- ".$option."\n";
            $position = strpos($option, "(");
            if ($position!==false) {
                $availableOptionsWithoutHints[] = trim(substr($option, 0, $position));
            } else {
                $availableOptionsWithoutHints[] = $option;
            }            
        }
        $result = readline("write one of above".($defaultOption!==null?" or hit enter to confirm '".$this->stripHint($availableOptions[$defaultOption])."'":"").": ");
        if (!$result) {
            if ($defaultOption!==null) {
                $result = $this->stripHint($availableOptions[$defaultOption]);
            } else {
                $this->error("Value cannot be empty!");
                return $this->singleSelect($message, $availableOptions, $defaultOption);
            }
        }
        if (!in_array($result, $availableOptionsWithoutHints)) {
            $this->error("Value '".$result."' is not supported!");
            return $this->singleSelect($message, $availableOptions, $defaultOption);
        }
        return $this->stripHint($result);
    }
    
    /**
     * Removes comment from line
     *
     * @param string $string
     * @return string
     */
    private function stripHint($string)
    {
        $position = strpos($string, "(");
        if ($position!==false) {
            return trim(substr($string, 0, $position));
        } else {
            return $string;
        }
    }
}

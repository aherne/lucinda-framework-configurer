<?php
namespace Lucinda\Configurer\Features;

/**
 * Parser of feature field documentation, helping to automate selection later on
 */
class DocBlockParser
{
    private ?string $handler = null;
    private string $type;
    private string $message;
    private array $options = [];
    private string $default;
    private bool $optional = false;
    private string $validator;
    private array $condition = [];
    
    /**
     * @param string $documentation
     */
    public function __construct(string $documentation)
    {
        $this->setHandler($documentation);
        $this->setType($documentation);
        $this->setMessage($documentation);
        $this->setOptions($documentation);
        $this->setDefaultOption($documentation);
        $this->setOptional($documentation);
        $this->setValidator($documentation);
        $this->setCondition($documentation);
    }
    
    /**
     * Detects class that handles the field based on @handler annotation
     *
     * @param string $documentation
     */
    private function setHandler(string $documentation): void
    {
        $matches = [];
        preg_match("/@handler\s([^\\n]+)/", $documentation, $matches);
        if (!empty($matches)) {
            $this->handler = trim($matches[1]);
        }
    }
    
    /**
     * Gets class that handles the field based on @handler annotation
     *
     * @return string|NULL
     */
    public function getHandler(): ?string
    {
        return $this->handler;
    }

    /**
     * Detects field type based on @var annotation
     *
     * @param string $documentation
     */
    private function setType(string $documentation): void
    {
        $matches = [];
        preg_match("/@var\s([^\\n]+)/", $documentation, $matches);
        $this->type = trim($matches[1]);
    }
    
    /**
     * Gets field type based on @var annotation
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
    
    /**
     * Detects message to prompt client based on @message annotation
     *
     * @param string $documentation
     */
    private function setMessage(string $documentation): void
    {
        $matches = [];
        preg_match("/@message\s([^\\n]+)/", $documentation, $matches);
        $this->message = trim($matches[1]);
    }
    
    /**
     * Gets message to prompt client
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
    
    /**
     * Detects options to display to client based on @option annotation
     *
     * @param string $documentation
     */
    private function setOptions(string $documentation): void
    {
        $matches = [];
        preg_match_all("/@option\s([^\\n]+)/", $documentation, $matches);
        $this->options = $matches[1];
        if ($this->type == "boolean" || strpos($this->type, "\\")===0) {
            $this->options = ["No","Yes"];
        }
    }
    
    /**
     * Gets options to display to client
     *
     * @return string
     */
    public function getOptions(): array
    {
        return $this->options;
    }
    
    /**
     * Detects default option index / default value to assume client has filled in case it hit enter on prompt based on @default annotation
     *
     * @param string $documentation
     */
    private function setDefaultOption(string $documentation): void
    {
        $matches = [];
        preg_match("/@default\s([^\\n]+)/", $documentation, $matches);
        $this->default = (isset($matches[1])?trim($matches[1]):"");
    }
    
    /**
     * Gets default option index / default value to assume client has filled in case it hit enter on prompt
     *
     * @return string
     */
    public function getDefaultOption(): string
    {
        return $this->default;
    }
    
    /**
     * Detects whether or not field will be optional based on @optional annotation
     *
     * @param string $documentation
     */
    private function setOptional(string $documentation): void
    {
        $this->optional = str_contains($documentation, "@optional");
    }
    
    
    /**
     * Gets whether field will be optional for client
     *
     * @return boolean
     */
    public function getOptional(): bool
    {
        return $this->optional;
    }
    
    /**
     * Detects regex to apply in field value validation based on @validator annotation
     *
     * @param string $documentation
     */
    private function setValidator(string $documentation): void
    {
        $matches = [];
        preg_match("/@validator\s([^\\n]+)/", $documentation, $matches);
        $validator = !empty($matches[1])?trim($matches[1]):"";
        if (!$validator) {
            if (!empty($this->options)) {
                $this->validator = "([0-".(sizeof($this->options)-1)."]{1})";
            } elseif ($this->type == "string") {
                $this->validator = "([\s\S]{1,})";
            } else {
                $this->validator = "";
            }
        } else {
            $this->validator = $validator;
        }
    }
    
    /**
     * Gets regex to apply in field value validation
     *
     * @return string
     */
    public function getValidator(): string
    {
        return $this->validator;
    }
    
    /**
     * Detects whether field should be displayed based on previous selections
     *
     * @param string $documentation
     */
    private function setCondition(string $documentation): void
    {
        $matches = [];
        preg_match("/@if\s([^=]+)=([a-zA-Z0-9',]+)/", $documentation, $matches);
        if (!empty($matches)) {
            $this->condition = [$matches[1]=>explode(",", $matches[2])];
        }
    }
    
    /**
     * Gets whether field should be displayed based on previous selections
     *
     * @return array
     */
    public function getCondition(): array
    {
        return $this->condition;
    }
}

<?php

namespace Best\DotNotation;

final class InconsistentKeyTypes extends Exception
{
    /**
     * @var mixed
     */
    private $originalValue;

    /**
     * @var mixed
     */
    private $newValue;

    /**
     * @var string
     */
    private $parentKeyPath;

    /**
     * InconsistentKeyTypes constructor.
     *
     * @param mixed $originalValue
     * @param string $parentKeyPath
     * @param string $message
     */
    public function __construct($originalValue, $newValue, $parentKeyPath, $message = "")
    {
        $this->originalValue = $originalValue;
        $this->newValue = $newValue;
        $this->parentKeyPath = $parentKeyPath;
        parent::__construct($message ?: $this->defaultMessage());
    }

    /**
     * Get the inconsistent value.
     *
     * @return mixed
     */
    public function getOriginalValue()
    {
        return $this->originalValue;
    }

    /**
     * Get the new value.
     *
     * @return mixed
     */
    public function getNewValue()
    {
        return $this->newValue;
    }

    /**
     * Get the parent key path that threw the exception.
     *
     * @return string
     */
    public function getParentKeyPath()
    {
        return $this->parentKeyPath;
    }

    /**
     * Get the default message from the properties.
     *
     * @return string
     */
    private function defaultMessage()
    {
        $valueIsArray = is_array($this->originalValue);
        $message = "Inconsistent type in dotted key: Attempting to change key '{$this->parentKeyPath}' ";
        $message .= ($valueIsArray ? "from an array to non-array" : "from a non-array to an array");
        return $message;
    }
}

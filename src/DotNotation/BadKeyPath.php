<?php

namespace Best\DotNotation;

class BadKeyPath extends Exception
{
    /**
     * @var mixed
     */
    private $keyPath;

    /**
     * BadKeyPath constructor.
     *
     * @param mixed $keyPath
     * @param string $message
     */
    public function __construct($keyPath, $message = "")
    {
        $this->keyPath = $keyPath;
        parent::__construct($message ?: $this->defaultMessage());
    }

    /**
     * Get the key path.
     *
     * @return mixed
     */
    public function getKeyPath()
    {
        return $this->keyPath;
    }

    /**
     * Get a default message based on the object's properties.
     *
     * @return string
     */
    private function defaultMessage()
    {
        return 'Variable is not a string or int: ' . var_export($this->keyPath, true);
    }

}
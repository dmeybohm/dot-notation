<?php

namespace Best\DotNotation;

class BadKey extends Exception
{
    /**
     * @var mixed
     */
    private $keyPath;

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
     */
    private function defaultMessage()
    {
        return 'Variable is not a string or int: ' . var_export($this->keyPath, true);
    }

}
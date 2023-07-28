<?php

namespace Best\DotNotation;

final class KeyNotFound extends Exception
{
    /**
     * @var string
     */
    private $keyPath;

    /**
     * KeyNotFound constructor.
     *
     * @param string $keyPath
     * @param string $message
     */
    public function __construct($keyPath, string $message = "")
    {
        $this->keyPath = $keyPath;
        parent::__construct($message ?: $this->defaultMessage());
    }

    /**
     * Set the default message.
     *
     * @return string
     */
    private function defaultMessage()
    {
        return sprintf("Key path '%s' not found", $this->keyPath);
    }

    /**
     * Get the key path.
     *
     * @return string
     */
    public function getKeyPath()
    {
        return $this->keyPath;
    }
}
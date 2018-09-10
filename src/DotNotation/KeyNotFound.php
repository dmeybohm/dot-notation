<?php

namespace Best\DotNotation;

final class KeyNotFound extends Exception
{
    const DEFAULT_MESSAGE_FORMAT = "Key path '%s' not found";

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
    public function __construct($keyPath, $message = "")
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
        return sprintf(self::DEFAULT_MESSAGE_FORMAT, $this->keyPath);
    }


}
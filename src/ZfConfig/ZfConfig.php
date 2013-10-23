<?php

namespace ZfConfig;

use ArrayObject;

/**
 * Class ZfConfig
 * @package ZfConfig
 */
class ZfConfig extends ArrayObject
{
    private $array;

    /**
     * Construct a configuration from an array.
     *
     * @param array $array Array to use.
     */
    public function __construct(array $array = array())
    {
        $this->array = $array;
    }

    /**
     * Convert the configuration to a normal PHP array, expanding the dotted keys.
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();

        foreach ($this->array as $key => $value)
        {
            $references = explode('.', $key);

            if (count($references) == 1)
            {
                $result[$key] = $this->getValue($value);
            }
            else
            {
                $this->appendDereference($result, $references, $value);
            }
        }

        return $result;
    }

    /**
     * Dereference an array of keys and append the result to an array.
     *
     * @param array &$result    The resulting array to append to.
     * @param array $references The dotted key as an array of strings.
     * @param mixed $value      The value the dotted key points to.
     * @return void
     */
    protected function appendDereference(array &$result, $references, $value)
    {
        $top = array_shift($references);
        $soFar = $this->getValue($value);
        foreach (array_reverse($references) as $ref)
        {
            $soFar = array($ref => $soFar);
        }

        if (isset($result[$top]))
        {
            $result[$top] = array_merge($result[$top], $soFar);
        }
        else
        {
            $result[$top] = $soFar;
        }
    }

    /**
     * Get a value and recursively resolve exploded references.
     *
     * @param mixed $value The value to get.
     * @return mixed
     */
    public function getValue($value)
    {
        if (is_array($value))
        {
            $recurseResult = new self($value);
            return $recurseResult->toArray();
        }
        else
        {
            return $value;
        }
    }
}
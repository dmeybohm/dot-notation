<?php

namespace ZfConfig;

/**
 * Class ZfConfig
 * @package ZfConfig
 */
class ZfConfig
{
    /**
     * Load a ZfConfig array from a file and convert it to a plain array.
     *
     * @param  string $path Path to the file.
     * @return array Array of configuration values.
     */
    public static function fromFile($path)
    {
        return self::expand(require $path);
    }

    /**
     * Convert the configuration to a normal PHP array, expanding the dotted keys.
     *
     * @param  array $array Array to expand.
     * @return array The expanded array.
     */
    public static function expand(array $array)
    {
        $result = array();

        foreach ($array as $key => $value)
        {
            $references = explode('.', $key);

            if (count($references) == 1)
            {
                $result[$key] = self::getValue($value);
            }
            else
            {
                self::appendDereference($result, $references, $value);
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
    private static function appendDereference(array &$result, $references, $value)
    {
        $top = array_shift($references);
        $soFar = self::getValue($value);
        $ref = end($references);
        while ($ref)
        {
            $soFar = array($ref => $soFar);
            $ref = prev($references);
        }

        if (isset($result[$top]))
        {
            $result[$top] = self::mergeArraysRecursively($result[$top], $soFar);
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
    public static function getValue($value)
    {
        if (is_array($value))
        {
            return self::expand($value);
        }
        else
        {
            return $value;
        }
    }

    /**
     * Merge arrays recursively, appending when the keys are arrays and overriding when not.
     *
     * @param  array $firstArray  First array to merge.
     * @param  array $secondArray Second array to merge.
     * @return array The merged array.
     */
    private static function mergeArraysRecursively(array $firstArray, array $secondArray)
    {
        $result = $firstArray;

        foreach ($secondArray as $key => $value)
        {
            if (isset($firstArray[$key]) && is_array($firstArray[$key]) && is_array($value))
            {
                $result[$key] = self::mergeArraysRecursively($firstArray[$key], $secondArray[$key]);
            }
            else
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
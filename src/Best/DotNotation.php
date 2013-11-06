<?php

namespace Best;

class DotNotation
{
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
            $references = self::expandKey($key);

            if (count($references) == 1)
            {
                $key = $references[0];
                $result[$key] = self::getValue($value);
            }
            else
            {
                self::dereferenceDots($result, $references, $value);
            }
        }

        return $result;
    }

    /**
     * Load a DotNotation array from a file and convert it to a plain array.
     *
     * @param  string $path Path to the file.
     * @return array Array of configuration values.
     */
    public static function fromFile($path)
    {
        return self::expand(require $path);
    }

    /**
     * Dereference an array of keys and append the result to an array.
     *
     * @param array &$result    The resulting array to append to.
     * @param array $references The dotted key as an array of strings.
     * @param mixed $value      The value the dotted key points to.
     * @return void
     */
    private static function dereferenceDots(array &$result, array $references, $value)
    {
        $top = array_shift($references);
        $values = self::getValue($value);

        $ref = end($references);
        while ($ref)
        {
            $values = array($ref => $values);
            $ref = prev($references);
        }

        if (isset($result[$top]))
        {
            $result[$top] = self::mergeArraysRecursively($result[$top], $values, array($top));
        }
        else
        {
            $result[$top] = $values;
        }
    }

    /**
     * Expand a key into an array of references.
     *
     * @param  string $key Key to expand.
     * @return array Array of references.
     */
    private static function expandKey($key)
    {
        $result = array();
        $hold   = "";

        $references = explode(".", $key);
        $last = count($references) - 1;
        foreach ($references as $i => $reference)
        {
            if ($reference[strlen($reference) - 1] === '\\' &&
                $i < $last)
            {
                $hold .= substr($reference, 0, -1) . ".";
            }
            else if (!empty($hold))
            {
                $result[] = $hold . $reference;
                $hold = "";
            }
            else
            {
                $result[] = $reference;
            }
        }

        return $result;
    }
    
    /**
     * Get a value and recursively resolve exploded references.
     *
     * @param mixed $value The value to get.
     * @return mixed
     */
    private static function getValue($value)
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
     * This is used instead of array_merge_recursive since that will turn duplicated non-array
     * values into arrays.
     *
     * @param  array $firstArray  First array to merge.
     * @param  array $secondArray Second array to merge.
     * @param  array $parentKeys  (Optional) Key path to parents used for error reporting.
     * @return array The merged array.
     * @throws \Best\DotNotation\KeyAlreadyExistsException if a key that already exists is changed to an
     * array.
     */
    private static function mergeArraysRecursively(array $firstArray, array $secondArray, array $parentKeys = array())
    {
        $result = $firstArray;

        foreach ($secondArray as $key => $value)
        {
            if (isset($firstArray[$key]))
            {
                $oneIsArray = is_array($firstArray[$key]);
                $twoIsArray = is_array($value);

                if ($oneIsArray xor $twoIsArray)
                {
                    $parentKeyComplete = join('.', array_merge($parentKeys, array($key)));
                    $message = "Inconsistent type in dotted key: Attempting to change key '{$parentKeyComplete}' ";
                    $message .= ($oneIsArray ? "from an array to non-array" : "from a non-array to an array");
                    throw new DotNotation\KeyAlreadyExistsException($message);
                }
                else if ($oneIsArray && $twoIsArray)
                {
                    array_push($parentKeys, $key);
                    $result[$key] = self::mergeArraysRecursively($firstArray[$key], $secondArray[$key], $parentKeys);
                    array_pop($parentKeys);
                }
                else
                {
                    $result[$key] = $value;
                }
            }
            else
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}

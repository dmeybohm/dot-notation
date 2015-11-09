<?php

namespace Best;

class DotNotation
{
    /**
     * Convert a php nested array to mongo like dot notation
     * @see http://stackoverflow.com/questions/10424335/php-convert-multidimensional-array-to-2d-array-with-dot-notation-keys
     * @param array $array
     * @return array
     */
    public static function compact(array $array)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
        $rVal = [];
        foreach ($iterator as $leafValue)
        {
            $keys = array();
            foreach (range(0, $iterator->getDepth()) as $depth)
                $keys[] = $iterator->getSubIterator($depth)->key();

            $rVal[ join('.', $keys) ] = $leafValue;
        }

        return $rVal;
    }
    
    /**
     * Convert a dot notation array to a normal PHP array, expanding the dotted keys.
     *
     * @param  array $array   Array to expand.
     * @return array The expanded array.
     */
    public static function expand(array $array)
    {
        $result = array();

        foreach ($array as $key => $value)
        {
            $references = self::expandKey($key);
            $value      = self::getValue($value);

            if (count($references) == 1)
            {
                $key = $references[0];

                // If the result key is already set, we have to merge
                // the two values.
                if (isset($result[$key]))
                {
                    $result[$key] = self::mergeTwoValues($result[$key], $value, array($key));
                }
                else
                {
                    $result[$key] = $value;
                }
            }
            else
            {
                self::dereferenceDots($result, $references, $value);
            }
        }

        return $result;
    }

    /**
     * Dereference an array of keys and append the result to an array.
     *
     * @param array &$result    The resulting array to append to.
     * @param array $references The dotted key as an array of strings.
     * @param mixed $values     The values the dotted key points to.
     * @return void
     */
    private static function dereferenceDots(array &$result, array $references, $values)
    {
        $top = array_shift($references);

        $ref = end($references);
        while ($ref)
        {
            $values = array($ref => $values);
            $ref = prev($references);
        }

        if (isset($result[$top]))
        {
            $result[$top] = self::mergeTwoValues($result[$top], $values, array($top));
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
     * Merge arrays recursively. 
     *
     * This merges the values when they are arrays, and overrides the value in 
     * the first array with the second otherwise. 
     *
     * This is used instead of array_merge_recursive since that will turn 
     * duplicated non-array values into arrays.
     *
     * @throws \Best\DotNotation\KeyAlreadyExistsException if a key that already exists is changed to an
     *         array, or if an array is changed to a string.
     * @param  array $firstArray  First array to merge.
     * @param  array $secondArray Second array to merge.
     * @param  array $parentKeys  Key path to parents used for error reporting.
     * @return array The merged array.
     */
    private static function mergeArraysRecursively(array $firstArray, array $secondArray, 
                                                   array $parentKeys)
    {
        $result = $firstArray;

        foreach ($secondArray as $key => $value)
        {
            if (isset($firstArray[$key]))
            {
                array_push($parentKeys, $key);
                $result[$key] = self::mergeTwoValues($firstArray[$key], $value, $parentKeys);
                array_pop($parentKeys);
            }
            else
            {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Merge two values and return the result.
     *
     * This merges the values when they are arrays, and overrides the value in 
     * the first array with the second otherwise. 
     *
     * @param  mixed $valueOne    First value to merge.
     * @param  mixed $valueTwo    Second value to merge.
     * @param  array $parentKeys  Key path to parents used for error reporting.
     * @throws \Best\DotNotation\KeyAlreadyExistsException if a key that already exists is changed to an
     *         array, or if an array is changed to a string.
     * @return The merged values as an array, or the second value if both are scalars.
     */
    private static function mergeTwoValues($valueOne, $valueTwo, array $parentKeys)
    {
        $oneIsArray = is_array($valueOne);
        $twoIsArray = is_array($valueTwo);

        if ($oneIsArray && $twoIsArray)
        {
            $result = self::mergeArraysRecursively($valueOne, $valueTwo, $parentKeys);
        }
        else if (!$oneIsArray && !$twoIsArray)
        {
            // Value from the second array overrides the first:
            $result = $valueTwo;
        }
        else
        {
            $result = self::handleInconsistentKeys($valueOne, $valueTwo, $parentKeys);
        }

        return $result;
    }

    /**
     * Handle changing a key to an array or vice-versa.
     *
     * @param  mixed $valueOne    First value.
     * @param  mixed $valueTwo    Second value.
     * @param  array $parentKeys  Key path to parents used for error reporting.
     * @throws \Best\DotNotation\KeyAlreadyExistsException
     * @return void
     */
    private static function handleInconsistentKeys($valueOne, $valueTwo, array $parentKeys)
    {
        $oneIsArray = is_array($valueOne);
        $twoIsArray = is_array($valueTwo);
        $parentKeyComplete = join('.', $parentKeys);
        $message = "Inconsistent type in dotted key: Attempting to change key '{$parentKeyComplete}' ";
        $message .= ($oneIsArray ? "from an array to non-array" : "from a non-array to an array");
        throw new DotNotation\KeyAlreadyExistsException($message);
    }
}

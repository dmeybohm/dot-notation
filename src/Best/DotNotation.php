<?php

namespace Best;

class DotNotation
{
    /**
     * Option to pass to expand() with the key to remap the overlapping
     * subkey to in the expanded array.
     *
     * @type integer
     */
    const RemapOverlappingToSubkey = 'RemapOverlappingToSubkey';

    /**
     * Convert the configuration to a normal PHP array, expanding the dotted keys.
     *
     * @param  array $array   Array to expand.
     * @param  array $options (Optional) Associative array of options.
     * @return array The expanded array.
     */
    public static function expand(array $array, array $options = array())
    {
        $result = array();

        foreach ($array as $key => $value)
        {
            $references = self::expandKey($key);

            if (count($references) == 1)
            {
                $key = $references[0];

                // If the result key is already set, we have to merge
                // the two values.
                if (isset($result[$key]))
                {
                    $result[$key] = self::mergeTwoValues($result[$key], self::getValue($value), $options, array($key));
                }
                else
                {
                    $result[$key] = self::getValue($value);
                }
            }
            else
            {
                self::dereferenceDots($result, $references, $value, $options);
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
     * @param array $options    Options.
     * @return void
     */
    private static function dereferenceDots(array &$result, array $references, $value, array $options)
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
            $result[$top] = self::mergeTwoValues($result[$top], $values, $options, array($top));
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
     * @param  array $options     Options.
     * @param  array $parentKeys  (Optional) Key path to parents used for error reporting.
     * @return array The merged array.
     */
    private static function mergeArraysRecursively(array $firstArray, array $secondArray, 
                                                   array $options, array $parentKeys = array())
    {
        $result = $firstArray;

        foreach ($secondArray as $key => $value)
        {
            if (isset($firstArray[$key]))
            {
                array_push($parentKeys, $key);
                $result[$key] = self::mergeTwoValues($firstArray[$key], $value, $options, $parentKeys);
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
     * Handle changing a key to an array or vice-versa.
     *
     * @param  mixed $valueOne    First value.
     * @param  mixed $valueTwo    Second value.
     * @param  array $options     Options.
     * @param  array $parentKeys  Key path to parents used for error reporting.
     * @throws \Best\DotNotation\KeyAlreadyExistsException If no override subkey is provided.
     * @return The merged 
     */
    public static function handleInconsistentKeys($valueOne, $valueTwo, $options, array $parentKeys)
    {
        if (isset($options[self::RemapOverlappingToSubkey])) 
        {
            $subkey = $options[self::RemapOverlappingToSubkey];

            if (is_array($valueOne)) 
            {
                $array = $valueOne;
                $scalar = $valueTwo;
            }
            else
            {
                $array = $valueTwo;
                $scalar = $valueOne;
            }

            $array[$subkey] = $scalar;
            return self::expand($array);
        } 
        else 
        {
            $oneIsArray = is_array($valueOne);
            $twoIsArray = is_array($valueTwo);
            $parentKeyComplete = join('.', $parentKeys);
            $message = "Inconsistent type in dotted key: Attempting to change key '{$parentKeyComplete}' ";
            $message .= ($oneIsArray ? "from an array to non-array" : "from a non-array to an array");
            throw new DotNotation\KeyAlreadyExistsException($message);
        }
    }

    /**
     * Merge two values and return the result.
     *
     * @throws \Best\DotNotation\KeyAlreadyExistsException if a key that already exists is changed to an
     *         array, or if an array is changed to a string.
     * @param  mixed $valueOne    First value to merge.
     * @param  mixed $valueTwo    Second value to merge.
     * @param  array $options     Options.
     * @param  array $parentKeys  Key path to parents used for error reporting.
     * @return The merged values as an array, or the second value if both are scalars.
     */
    private static function mergeTwoValues($valueOne, $valueTwo, array $options, array $parentKeys)
    {
        $oneIsArray = is_array($valueOne);
        $twoIsArray = is_array($valueTwo);

        if ($oneIsArray xor $twoIsArray)
        {
            $result = self::handleInconsistentKeys($valueOne, $valueTwo, 
                                                   $options, $parentKeys);
        }
        else if ($oneIsArray && $twoIsArray)
        {
            $result = self::mergeArraysRecursively($valueOne, $valueTwo,
                                                   $options, $parentKeys);
        }
        else
        {
            // Value from the second array overrides the first:
            $result = $valueTwo;
        }

        return $result;
    }
}

<?php

namespace Best;

use Best\DotNotation\BadKeyPath;
use Best\DotNotation\InconsistentKeyTypes;
use Best\DotNotation\KeyNotFound;

final class DotNotation
{
    /**
     * DotNotation constructor.
     */
    private function __construct()
    {
    }

    /**
     * Get the dotted key path value from the array.
     *
     * @param array $array
     * @param string $keyPath
     *
     * @return mixed The returned value, or the default value if no value is found.
     * @throws KeyNotFound
     * @throws BadKeyPath
     */
    public static function get(array $array, $keyPath)
    {
        self::checkKeyPath($keyPath);
        $keys = self::explodeKeys(strval($keyPath));

        while ($keys) {
            $key = array_shift($keys);

            if (array_key_exists($key, $array)) {
                $nextValue = $array[$key];
                if (!$keys) {
                    return $nextValue;
                }
                elseif (!is_array($nextValue)) {
                    break;
                }
                $array = $nextValue;
            }
            else {
                break;
            }
        }

        throw new KeyNotFound($keyPath);
    }

    /**
     * Get the dotted key path value from the array. Return a default value if the value is not found.
     *
     * @param array $array
     * @param string $keyPath
     * @param mixed $defaultValue
     *
     * @return mixed The returned value, or the default value if no value is found.
     */
    public static function getOrDefault(array $array, $keyPath, $defaultValue = null)
    {
        try {
            return self::get($array, $keyPath);
        }
        catch (KeyNotFound $e) {
            return $defaultValue;
        }
    }

    /**
     * Set the dotted key path in the array.
     *
     * @param array $array
     * @param string $keyPath
     * @param mixed $value The value to set in the array.
     *
     * @return array The resulting array with the value set.
     * @throws BadKeyPath
     * @throws InconsistentKeyTypes
     */
    public static function set(array $array, $keyPath, $value)
    {
        self::checkKeyPath($keyPath);
        $keys = self::explodeKeys(strval($keyPath));
        $result = $array;
        $ptr = &$result;

        $parentKeys = array();
        while ($keys) {
            $key = array_shift($keys);
            $parentKeys[] = $key;

            if (array_key_exists($key, $ptr)) {
                $ptr = &$ptr[$key];
                if (!$keys) {
                    $ptr = $value;
                    break;
                }
                elseif (!is_array($ptr)) {
                    self::throwInconsistentKeyTypes($ptr, $parentKeys);
                }
            }
            else {
                $ptr[$key] = $keys ? array() : $value;
                $ptr = &$ptr[$key];
            }
        }

        return $result;
    }

    /**
     * Set the dotted key path in the array and return the value.
     *
     * If any key already exists and is not an array, convert the key to an array so
     * that the value can be set.
     *
     * @param array $array
     * @param string $keyPath
     * @param mixed $value The value to set in the array.
     *
     * @return array The resulting array with the value set.
     * @throws BadKeyPath
     */
    public static function setAndOverride(array $array, $keyPath, $value)
    {
        self::checkKeyPath($keyPath);
        $keys = self::explodeKeys(strval($keyPath));
        $result = $array;
        $ptr = &$result;

        $parentKeys = array();
        while ($keys) {
            $key = array_shift($keys);
            $parentKeys[] = $key;

            if (array_key_exists($key, $ptr)) {
                $ptr = &$ptr[$key];
                if (!$keys) {
                    $ptr = $value;
                    break;
                }
                elseif (!is_array($ptr)) {
                    $ptr = array();
                }
            }
            else {
                $ptr[$key] = $keys ? array() : $value;
                $ptr = &$ptr[$key];
            }
        }

        return $result;
    }

    /**
     * Remove a value from a key path in an array and return the array.
     *
     * @param array $array
     * @param string $keyPath
     * @return array The array with the value unset.
     *
     * @throws KeyNotFound
     * @throws BadKeyPath
     */
    public static function remove(array $array, $keyPath)
    {
        self::checkKeyPath($keyPath);
        $keys = self::explodeKeys(strval($keyPath));
        $result = $array;
        $ptr = &$result;

        while ($keys) {
            $key = array_shift($keys);

            if (array_key_exists($key, $ptr)) {
                $prevPtr = &$ptr;
                $ptr = &$ptr[$key];
                if (!$keys) {
                    unset($prevPtr[$key]);
                    return $result;
                }
                elseif (!is_array($ptr)) {
                    throw new KeyNotFound($keyPath);
                }
            }
            else {
                $ptr[$key] = array();
                $ptr = &$ptr[$key];
            }
        }

        throw new KeyNotFound($keyPath);
    }

    /**
     * Remove a value from a key path in an array and return the array.
     *
     * If the keyPath does not exist, returns the array unmodified.
     *
     * @param array $array
     * @param string $keyPath
     *
     * @return array The array with the value unset, or the original array if the key does not exist.
     */
    public static function removeIfExists(array $array, $keyPath)
    {
        try {
            return self::remove($array, $keyPath);
        }
        catch (KeyNotFound $e) {
            return $array;
        }
    }

    /**
     * Convert a dot notation array to a normal PHP array, recursively expanding the dotted keys.
     *
     * @param  array $array Array to expand.
     *
     * @return array The expanded array.
     * @throws InconsistentKeyTypes
     */
    public static function expand(array $array)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $references = self::explodeKeys((string)$key); // cast for performance
            $value = self::expandValue($value);

            if (count($references) === 1) {
                $key = $references[0];

                // If the result key is already set, we have to merge
                // the two values.
                if (array_key_exists($key, $result)) {
                    $result[$key] = self::mergeTwoValues($result[$key], $value, array($key));
                }
                else {
                    $result[$key] = $value;
                }
            }
            else {
                self::dereferenceDots($result, $references, $value);
            }
        }

        return $result;
    }

    /**
     * Compact an expanded array to a DotNotation array.
     *
     * @param array $array The array to compact.
     *
     * @return array The compacted array.
     */
    public static function compact(array $array)
    {
        $result = array();

        foreach ($array as $key => $value) {
            $escapedKey = self::escapeKey($key);
            $extraKeyPath = "";

            while (self::isCompactableArray($value)) {
                $nextValue = reset($value);
                $compactKey = key($value);

                $extraKeyPath .= "." . self::escapeKey($compactKey);
                $value = $nextValue;
            }

            $result[$escapedKey . $extraKeyPath] = self::compactValue($value);
        }

        return $result;
    }

    /**
     * Escape keys.
     *
     * @param string $key The key to escape.
     *
     * @return string
     */
    private static function escapeKey($key)
    {
        return str_replace(".", "\\.", $key);
    }

    /**
     * Create keys from the value.
     *
     * @param mixed $value The value to compact keys for.
     *
     * @return mixed The compacted value.
     */
    private static function compactValue($value)
    {
        if (is_array($value)) {
            return self::compact($value);
        }
        else {
            return $value;
        }
    }

    /**
     * Dereference an array of keys and append the result to an array.
     *
     * @param array &$result The resulting array to append to.
     * @param array $references The dotted key as an array of strings.
     * @param mixed $values The values the dotted key points to.
     *
     * @return void
     * @throws InconsistentKeyTypes
     */
    private static function dereferenceDots(&$result, $references, $values)
    {
        $top = array_shift($references);

        $ref = end($references);
        while ($ref) {
            $values = array($ref => $values);
            $ref = prev($references);
        }

        if (array_key_exists($top, $result)) {
            $result[$top] = self::mergeTwoValues($result[$top], $values, array($top));
        }
        else {
            $result[$top] = $values;
        }
    }

    /**
     * Whether the value is an integer.
     *
     * @param mixed $value
     * @return bool
     */
    private static function isInteger($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Get a value and recursively resolve exploded references.
     *
     * @param mixed $value The value to get.
     *
     * @return mixed
     * @throws InconsistentKeyTypes
     */
    private static function expandValue($value)
    {
        if (is_array($value)) {
            return self::expand($value);
        }
        else {
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
     * @throws \Best\DotNotation\InconsistentKeyTypes if a key that already exists is changed to an
     *         array, or if an array is changed to a string.
     *
     * @param  array $firstArray First array to merge.
     * @param  array $secondArray Second array to merge.
     * @param  array $parentKeys Key path to parents used for error reporting.
     *
     * @return array The merged array.
     */
    private static function mergeArraysRecursively($firstArray, $secondArray, $parentKeys)
    {
        $result = $firstArray;

        foreach ($secondArray as $key => $value) {
            if (array_key_exists($key, $firstArray)) {
                array_push($parentKeys, $key);
                $result[$key] = self::mergeTwoValues($firstArray[$key], $value, $parentKeys);
                array_pop($parentKeys);
            }
            else {
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
     * @param  mixed $originalValue First value to merge.
     * @param  mixed $newValue Second value to merge.
     * @param  array $parentKeys Key path to parents used for error reporting.
     *
     * @throws \Best\DotNotation\InconsistentKeyTypes if a key that already exists is changed to an
     *         array, or if an array is changed to a string.
     * @return array The merged values as an array, or the second value if both are scalars.
     */
    private static function mergeTwoValues($originalValue, $newValue, $parentKeys)
    {
        $originalIsArray = is_array($originalValue);
        $newIsArray = is_array($newValue);

        if ($originalIsArray && $newIsArray) {
            $result = self::mergeArraysRecursively($originalValue, $newValue, $parentKeys);
        }
        elseif (!$originalIsArray && !$newIsArray) {
            // Value from the second array overrides the first:
            $result = $newValue;
        }
        else {
            self::throwInconsistentKeyTypes($originalValue, $parentKeys);
        }

        return $result;
    }

    /**
     * Handle changing a key to an array or vice-versa.
     *
     * @param  mixed $originalValue First value.
     * @param  array $parentKeys Key path to parents used for error reporting.
     *
     * @return void
     * @throws InconsistentKeyTypes
     */
    private static function throwInconsistentKeyTypes($originalValue, array $parentKeys)
    {
        $parentKeyPath = implode('.', $parentKeys);
        throw new InconsistentKeyTypes($originalValue, $parentKeyPath);
    }

    /**
     * Check the variable is a string.
     *
     * @param mixed $keyPath
     *
     * @throws BadKeyPath
     */
    private static function checkKeyPath($keyPath)
    {
        if (!is_string($keyPath) && !is_int($keyPath)) {
            throw new BadKeyPath($keyPath);
        }
    }

    /**
     * Whether the array is "compactable".
     *
     * An array is compactable if it has only one key that is not an integer or integer-like string.
     *
     * @param mixed $value
     * @return bool
     */
    private static function isCompactableArray($value)
    {
        if (!is_array($value)) {
            return false;
        }
        reset($value);
        $firstKey = key($value);
        if (self::isInteger($firstKey)) {
            return false;
        }
        next($value);
        return key($value) === null; // if the array only has one key, then the second key is null.
    }

    /**
     * Explode the keys.
     *
     * If a key contains backslashes before any dots, then don't explode based on that dot.
     *
     * @param string $keyPath
     * @return array
     */
    private static function explodeKeys($keyPath)
    {
        $keys = explode('.', $keyPath);
        if (strpos($keyPath, '\\.') === false) {
            return $keys;
        }

        //
        // This is the slow path, where the keys contain escaped dots:
        //
        $joinKeys = array();
        foreach ($keys as $index => $key) {
            if ($key[strlen($key) - 1] === '\\') {
                $joinKeys[$index + 1] = true;
            }
        }

        $result = array();
        $next = 0;
        foreach ($keys as $index => $key) {
            if (array_key_exists($index, $joinKeys)) {
                $result[$next - 1] = sprintf("%s.%s", substr($result[$next - 1], 0, -1), $key);
            }
            else {
                $result[$next] = $key;
                $next += 1;
            }
        }

        return $result;
    }
}

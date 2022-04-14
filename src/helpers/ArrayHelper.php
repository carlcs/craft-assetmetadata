<?php

namespace carlcs\assetmetadata\helpers;

class ArrayHelper
{
    /**
     * Traverse an array using dot notation.
     * @see https://selv.in/blog/traversing-arrays-using-dot-notation
     */
    public static function getValueByKey(string $path, array $data): mixed
    {
        if (str_contains($path, '.')) {
            foreach (explode('.', $path) as $key) {
                if (!array_key_exists($key, $data)) {
                    return null;
                }

                // Continue traversing the array.
                $data = $data[$key];
            }

            return $data;
        }

        return $data[$path] ?? null;
    }
}

<?php

namespace App\Traits;

trait AppTraits
{
    /**
     * Create pivot array from given values
     *
     * @param array $entities
     * @param array $pivots
     * @return array combined $pivots
     */
    public static function combinePivot($entities, $pivots = [])
    {
        // Set array
        $pivotArray = [];
        // Loop through all pivot attributes
        foreach ($pivots as $pivot => $value) {
            // Combine them to pivot array
            $pivotArray += [$pivot => $value];
        }
        // Get the total of arrays we need to fill
        $total = count($entities);
        // Make filler array
        $filler = array_fill(0, $total, $pivotArray);
        // Combine and return filler pivot array with data
        return array_combine($entities, $filler);
    }
}
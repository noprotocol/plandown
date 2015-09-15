<?php

use Sledgehammer\Object;

/**
 * The plandown parser
 */
class Plandown extends Object {

    static $unit2factor = [
        'p' => 1,
        'point' => 1,
        'points' => 1,
        'u' => 1,
        'uur' => 1,
        'uren' => 1,
        'h' => 1,
        'hour' => 1,
        'hours' => 1,
        'm' => 1 / 60,
        'min' => 1 / 60,
    ];

    /**
     * 
     * @param string $plandown
     */
    static function parse($plandown) {
        $stories = [];
        $epic = false;
        $unitRegex = implode('|', array_map(function ($unit) {
                return preg_quote($unit, '');
            }, array_keys(static::$unit2factor)));
        $lines = explode("\n", $plandown);
        foreach ($lines as $i => $text) {
            $linenr = $i + 1;
            $text = trim($text);
            if ($text === '') {
                continue; // skip empty lines
            }
            if (substr($text, 0, 1) === '#') { // Epic definition?
                $epic = ltrim($text, "# \t");
                continue;
            } else if (!$epic) {
                throw new Sledgehammer\InfoException('Plandown document must start with a epic definition', ['Hint' => 'An epic starts with one ore more "#"', 'Example' => '# My first epic']);
            }
            $text = ltrim($text, "-* \t"); // strip markdown summary notation
            if (preg_match('/\s*(?P<amount>[0-9.,]+)\s*(?P<unit>' . $unitRegex . ')$/i', $text, $match)) {
                $unit = strtolower($match['unit']);
                $story = [
                    'epic' => $epic,
                    'summary' => substr($text, 0, -1 * strlen($match[0])),
                    'points' => round(str_replace(',', '.', $match['amount']) * static::$unit2factor[$unit], 2),
                ];
                $stories[] = $story;
            } else {
                throw new Sledgehammer\InfoException('No points detected in "' . $text . '" on line ' . $linenr, ['Hint' => 'An epic starts with one ore more "#"', 'Example' => '# My first epic']);
            }
        }
        return $stories;
    }

}

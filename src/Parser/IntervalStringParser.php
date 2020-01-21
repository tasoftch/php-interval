<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TASoft\Util\Interval\Parser;


use TASoft\Util\Interval\Component\IntervalComponent;
use TASoft\Util\Interval\Exception\ScheduleIntervalStringParseException;
use TASoft\Util\Interval\Interval;
use TASoft\Util\Interval\IntervalInterface;
use TASoft\Util\ValueInjector;

class IntervalStringParser
{
    public static $monthNames        = [
        'JAN' => 1,
        'FEB' => 2,
        'MAR' => 3,
        'APR' => 4,
        'MAY' => 5,
        'JUN' => 6,
        'JUL' => 7,
        'AUG' => 8,
        'SEP' => 9,
        'OCT' => 10,
        'NOV' => 11,
        'DEC' => 12
    ];

    public static $weekDayNames    = [
        'SUN' => 0,
        'MON' => 1,
        'TUE' => 2,
        'WED' => 3,
        'THU' => 4,
        'FRI' => 5,
        'SAT' => 6
    ];

    public static $minimumYear = 1970;
    public static $maximumYear = 2030;


    public static function parse(string $intervalString): ?IntervalInterface {
        if(count($elements = preg_split('/\s+/', $intervalString)) < 5)
            throw new ScheduleIntervalStringParseException('Invalid schedule string.');

        $min = static::interpret($elements[0], 0, 59, "minutes");
        $hour = static::interpret($elements[1], 0, 23, "hours");
        $days = static::interpret($elements[2], 1, 31, "daysOfMonth");
        $month = static::interpret($elements[3], 1, 12, "months", static::$monthNames);
        $week = static::interpret($elements[4], 0, 6, "daysOfWeek", static::$weekDayNames);
        if(isset($elements[5]))
            $year = static::interpret($elements[5], static::$minimumYear, static::$maximumYear, "years");
        else
            $year = [];

        if($min||$hour||$days||$month||$week) {
            $intv = new Interval();
            $vi = new ValueInjector($intv);
            $vi->_minutes = $min;
            $vi->_hours = $hour;
            $vi->_days = $days;
            $vi->_months = $month;
            $vi->_weekdays = $week;
            $vi->_years = $year;
            return $intv;
        }
        return NULL;
    }


    /**
     * Parse a single interval component
     *
     * @param string $specification
     * @param int $rangeMinimum
     * @param int $rangeMaximum
     * @param string $errorName
     * @param array $namedItems
     * @return array
     * @throws ScheduleIntervalStringParseException
     */
    public static function interpret(string $specification, int $rangeMinimum, int $rangeMaximum, string $errorName = 'error', array $namedItems = [])
    {
        if ((!is_string($specification)) && (!(is_int($specification))))
            throw new ScheduleIntervalStringParseException('Invalid specification.');

        $specs = array();
        $arrSegments = explode(',', $specification);

        foreach ($arrSegments as $segment) {
            $hasRange = (($posRange = strpos($segment, '-')) !== FALSE);
            $hasInterval = (($posIncrement = strpos($segment, '/')) !== FALSE);

            if ($hasRange && $hasInterval && $posIncrement < $posRange) {
                $e = new ScheduleIntervalStringParseException("Invalid order ($errorName)");
                $e->setSpecificationName($errorName);
                throw $e;
            }

            $segmentNumber1 = $segment;
            $segmentNumber2 = '';
            $segmentIncrement = '';
            $intIncrement = 1;
            if ($hasInterval) {
                $segmentNumber1 = substr($segment, 0, $posIncrement);
                $segmentIncrement = substr($segment, $posIncrement + 1);
            }
            if ($hasRange) {
                $segmentNumber2 = substr($segmentNumber1, $posRange + 1);
                $segmentNumber1 = substr($segmentNumber1, 0, $posRange);
            }
            // Get and validate first value in range
            if ($segmentNumber1 == '*') {
                $intNumber1 = $rangeMinimum;
                $intNumber2 = $rangeMaximum;
                $hasRange = TRUE;
            } else {
                $invalidSymbolException = function() use ($errorName) {
                    $e = new ScheduleIntervalStringParseException("Invalid symbol ($errorName).");
                    $e->setSpecificationName($errorName);
                    throw $e;
                };
                $outOfBoundsException = function() use ($errorName) {
                    $e = new ScheduleIntervalStringParseException("Out of bounds ($errorName).");
                    $e->setSpecificationName($errorName);
                    throw $e;
                };

                if (array_key_exists(strtoupper($segmentNumber1), $namedItems))
                    $segmentNumber1 = $namedItems[strtoupper($segmentNumber1)];

                if (((string)($intNumber1 = (int)$segmentNumber1)) != $segmentNumber1)
                    $invalidSymbolException();

                if (($intNumber1 < $rangeMinimum) || ($intNumber1 > $rangeMaximum)) {
                    $e = new ScheduleIntervalStringParseException("Out of bounds ($errorName).");
                    $e->setSpecificationName($errorName);
                    throw $e;
                }
                // Get and validate second value in range
                if ($hasRange) {
                    if (array_key_exists(strtoupper($segmentNumber2), $namedItems)) $segmentNumber2 = $namedItems[strtoupper($segmentNumber2)];
                    if (((string)($intNumber2 = (int)$segmentNumber2)) != $segmentNumber2)
                        $invalidSymbolException();

                    if (($intNumber2 < $rangeMinimum) || ($intNumber2 > $rangeMaximum))
                        $outOfBoundsException();

                    if ($intNumber1 > $intNumber2) {
                        $e = new ScheduleIntervalStringParseException("Invalid Range ($errorName).");
                        $e->setSpecificationName($errorName);
                        throw $e;
                    }
                }
            }

            if ($hasInterval) {
                if (($intIncrement = (int)$segmentIncrement) != $segmentIncrement)
                    $invalidSymbolException();

                if ($intIncrement < 1)
                    $outOfBoundsException();
            }

            if ($hasRange) {
                $specs[] = new IntervalComponent($intNumber1, $intIncrement, $intNumber2);
            } else {
                $specs[] = new IntervalComponent($intNumber1);
            }
        }

        return $specs;
    }
}
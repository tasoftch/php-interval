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

namespace TASoft\Util\Interval;


use DateTime;
use TASoft\Util\Interval\Component\IntervalComponentInterface;
use TASoft\Util\Interval\Exception\ScheduleIntervalMatchException;
use TASoft\Util\Interval\Parser\IntervalStringParser;

class Interval implements IntervalInterface
{
    private $_minutes;
    private $_hours;
    private $_days;
    private $_months;
    private $_weekdays;
    private $_years;

    private $_minuteItems;
    private $_hourItems;
    private $_dayItems;
    private $_monthItems;
    private $_weekdayItems;
    private $_yearsItems;

    /**
     * @inheritDoc
     */
    public function getMinuteComponents(): array
    {
        return $this->_minutes;
    }

    /**
     * @inheritDoc
     */
    public function getHourComponents(): array
    {
        return $this->_hours;
    }

    /**
     * @inheritDoc
     */
    public function getDayComponents(): array
    {
        return $this->_days;
    }

    /**
     * @inheritDoc
     */
    public function getMonthComponents(): array
    {
        return $this->_months;
    }

    /**
     * @inheritDoc
     */
    public function getWeekdayComponents(): array
    {
        return $this->_weekdays;
    }

    /**
     * @inheritDoc
     */
    public function getYearComponents(): array
    {
        return $this->_years;
    }

    /**
     * Gets all available minutes in an hour
     *
     * @return int[]
     */
    public function getMinuteItems()
    {
        $this->createItems();
        return $this->_minuteItems;
    }

    /**
     * Gets all available hours in a day
     *
     * @return int[]
     */
    public function getHourItems()
    {
        $this->createItems();
        return $this->_hourItems;
    }

    /**
     * Gets all available days in a month
     *
     * @return int[]
     */
    public function getDayItems()
    {
        $this->createItems();
        return $this->_dayItems;
    }

    /**
     * Gets all available month in a year
     *
     * @return int[]
     */
    public function getMonthItems()
    {
        $this->createItems();
        return $this->_monthItems;
    }

    /**
     * Gets all available weekdays
     *
     * @return int[]
     */
    public function getWeekdayItems()
    {
        $this->createItems();
        return $this->_weekdayItems;
    }

    /**
     * Gets all available years
     *
     * @return int[]
     */
    public function getYearItems()
    {
        $this->createItems();
        return $this->_yearsItems;
    }

    /**
     * Private method to transform the components into real numeric representation of their specifications
     *
     * @internal
     */
    private function createItems() {
        if(!$this->_minuteItems) {
            $handler = function($components) {
                $items = [];

                /** @var IntervalComponentInterface $component */
                foreach($components as $component)
                {
                    if(!$component->hasInterval())
                        $items[ $component->getMinimum() ] = TRUE;
                    else
                        for($number = $component->getMinimum(); $number <= $component->getMaximum(); $number += $component->getInterval())
                            $items[$number] = TRUE;
                }
                ksort($items);
                return array_keys($items);
            };

            $this->_minuteItems = $handler( $this->_minutes );
            $this->_hourItems = $handler( $this->_hours );
            $this->_dayItems = $handler( $this->_days );
            $this->_monthItems = $handler( $this->_months );
            $this->_weekdayItems = $handler( $this->_weekdays );
            if($this->_years)
                $this->_yearsItems = $handler( $this->_years );
        }
    }

    /**
     * @inheritDoc
     */
    public function match(DateTime $dateTime, bool $shouldThrowException = false): bool
    {
        $throwException = function($expected, $received, $name) use ($shouldThrowException) {
            if($shouldThrowException) {
                $e = new ScheduleIntervalMatchException("Value $received did not match at $name (Expected: %s)", 0, NULL,  implode(", ", $expected));
                $e->setSpecificationName($name);
                $e->setExpectedValues($expected);
                $e->setReceivedValue($received);
                throw $e;
            }
            return false;
        };

        if($this->_years && !in_array( $vl = $dateTime->format("Y")*1, $ex = $this->getYearItems() ))
            return $throwException($ex, $vl, "years");

        if(!in_array($vl = $dateTime->format("w"), $ex = $this->getWeekdayItems()))
            return $throwException($ex,$vl,"weekdays");

        if(!in_array($vl = $dateTime->format("n"), $ex = $this->getMonthItems()))
            return $throwException($ex,$vl,"months");

        if(!in_array($vl = $dateTime->format("j"), $ex = $this->getDayItems()))
            return $throwException($ex,$vl,"days");

        if(!in_array($vl = $dateTime->format("G"), $ex = $this->getHourItems()))
            return $throwException($ex,$vl,"hours");

        if(!in_array($vl = $dateTime->format("i"), $ex = $this->getMinuteItems()))
            return $throwException($ex,$vl,"minutes");
        return true;
    }

    /**
     * @inheritDoc
     */
    public function next(DateTime $dateTime): ?DateTime
    {
        list($year, $months, $days, $hours, $minutes) = [
            $dateTime->format("Y") * 1,
            $dateTime->format("n") * 1,
            $dateTime->format("j") * 1,
            $dateTime->format("G") * 1,
            $dateTime->format("i") * 1
        ];

        $earliest = function($arrItems, $afterItem = FALSE, &$overflow = false)
        {

            if($afterItem === FALSE)
            {
                reset($arrItems);
                if($overflow)
                    next($arrItems);
                $overflow = false;
                return current($arrItems);
            }

            $overflow = false;
            foreach($arrItems as $value) {
                if ($value > $afterItem)
                    return $value;
            }

            $overflow = true;
            reset($arrItems);
            return current($arrItems);
        };

        $increase = function ($arrItems, $rangeMin, $rangeMax, & $current) use ($earliest)
        {
            $current++;
            if($current < $rangeMin)
                $current = $earliest($arrItems);
            for(;$current <= $rangeMax; $current++)
                if(in_array($current, $arrItems))
                    return FALSE;
            $current = $earliest($arrItems);
            return TRUE;
        };

        while(1)
        {
            $overflow=false;

            if($this->_years && !in_array($year, $list = $this->getYearItems()))
            {
                if(($year = $earliest($list, $year, $overflow)) === FALSE)
                    return NULL;

                $months = $earliest($this->getMonthItems());
                $days = $earliest($this->getDayItems());
                $hours = $earliest($this->getHourItems());
                $minutes = $earliest($this->getMinuteItems());
                break;
            } elseif(!in_array($months, $list = $this->getMonthItems()))
            {
                $months = $earliest($list, $months, $overflow);
                $days = $earliest($this->getDayItems());
                $hours = $earliest($this->getHourItems());
                $minutes = $earliest($this->getMinuteItems());

                if($overflow) {
                    if($this->_years)
                        $months = $earliest($this->getYearItems(), $year);
                    else
                        $year++;
                }

                break;
            } elseif(!in_array($days, $list = $this->getDayItems()))
            {
                $days = $earliest($list, $days, $overflow);
                $hours = $earliest($this->getHourItems());
                $minutes = $earliest($this->getMinuteItems());

                if($overflow) {
                    $overflow = false;
                    $months = $earliest($this->getMonthItems(), $months, $overflow);
                    if($overflow) {
                        if($this->_years)
                            $months = $earliest($this->getYearItems(), $year);
                        else
                            $year++;
                    }
                }
                break;
            } elseif(!in_array($hours, $list = $this->getHourItems()))
            {
                $hours = $earliest($list, $hours, $overflow);
                $minutes = $earliest($this->getMinuteItems());

                if($overflow) {
                    $overflow = false;
                    $days = $earliest($this->getDayItems(), $days, $overflow);
                    if($overflow) {
                        $overflow = false;
                        $months = $earliest($this->getMonthItems(), $months, $overflow);
                        if($overflow) {
                            if($this->_years)
                                $months = $earliest($this->getYearItems(), $year);
                            else
                                $year++;
                        }
                    }
                }
                break;
            } elseif(!in_array($minutes, $list = $this->getMinuteItems()))
            {
                $minutes = $earliest($list, $minutes, $overflow);

                if($overflow) {
                    $overflow = false;
                    $hours = $earliest($this->getHourItems(), $hours, $overflow);
                    if($overflow) {
                        $overflow = false;
                        $days = $earliest($this->getDayItems(), $days, $overflow);
                        if($overflow) {
                            $overflow = false;
                            $months = $earliest($this->getMonthItems(), $months, $overflow);
                            if($overflow) {
                                if($this->_years)
                                    $months = $earliest($this->getYearItems(), $year);
                                else
                                    $year++;
                            }
                        }
                    }
                }
                break;
            }

            if($increase($this->getMinuteItems(), 0, 59, $minutes)) {
                if($increase($this->getHourItems(), 0, 23, $hours)) {
                    $daysInMonth = (new DateTime(sprintf("%d-%02d-%02d", $year, $months, $days)))->format("t") * 1;
                    if($increase($this->getDayItems(), 1, $daysInMonth, $days)) {
                        if($increase($this->getMonthItems(), 1, 12, $months)) {
                            if($this->_years) {
                                $increase($this->getYearItems(), IntervalStringParser::$minimumYear, IntervalStringParser::$maximumYear, $year);
                            } else {
                                $year++;
                            }
                        }
                    }
                }
            }

            break;
        }


        $dateTime->setDate($year, $months, $days);
        $dateTime->setTime($hours, $minutes);

        if(!in_array($dateTime->format("w"), $this->getWeekdayItems())) {
            $dateTime->modify("+1day");
            $dateTime->setTime(0,0);
            return $this->next($dateTime);
        }

        return $dateTime;
    }

    /**
     * @inheritDoc
     */
    public function stringify(bool $useNames = true): string
    {
        $stringify = function($components, $min, $max, $names = []) {
            $strs = [];
            /** @var IntervalComponentInterface $component */
            foreach ($components as $component) {
                $strs[] = $component->stringify($min, $max, $names);
            }
            return implode(",", $strs);
        };

        $string = sprintf("%s %s %s %s %s",
            $stringify($this->_minutes, 0, 59),
            $stringify($this->_hours, 0, 23),
            $stringify($this->_days, 1, 31),
            $stringify($this->_months, 1, 12, $useNames ? IntervalStringParser::$monthNames : []),
            $stringify($this->_weekdays, 0, 6, $useNames ? IntervalStringParser::$weekDayNames : [])
        );

        if($this->_years) {
            $string .= " " . $stringify($this->_years, IntervalStringParser::$minimumYear, IntervalStringParser::$maximumYear);
        }

        return $string;
    }

    /**
     * Checks, if a given interval intersects with
     *
     * @param IntervalInterface $otherInterval
     * @return bool
     */
    public function hasIntersection(IntervalInterface $otherInterval): bool {
        $diff = array_diff($otherInterval->getMinuteItems(), $this->getMinuteItems());
        if($diff)
            return false;
        $diff = array_diff($otherInterval->getHourItems(), $this->getHourItems());
        if($diff)
            return false;
        $diff = array_diff($otherInterval->getDayItems(), $this->getDayItems());
        if($diff)
            return false;
        $diff = array_diff($otherInterval->getMonthItems(), $this->getMonthItems());
        if($diff)
            return false;
        $diff = array_diff($otherInterval->getWeekdayItems(), $this->getWeekdayItems());
        if($diff)
            return false;

        return true;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->stringify();
    }
}
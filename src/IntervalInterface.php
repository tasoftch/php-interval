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

interface IntervalInterface
{
    /**
     * Checks, if a given date matches an interval specification
     *
     * @param DateTime $dateTime
     * @param bool $shouldThrowException
     * @return bool
     * @throws ScheduleIntervalMatchException
     */
    public function match(DateTime $dateTime, bool $shouldThrowException = true): bool;

    /**
     * Applies the interval to a given date
     *
     * @param DateTime $dateTime
     * @return DateTime|null
     */
    public function next(DateTime $dateTime): ?DateTime;

    /**
     * Transforms the interval into a string value
     *
     * @param bool $useNames
     * @return string
     */
    public function stringify(bool $useNames = true): string;

    /**
     * @return IntervalComponentInterface[]
     */
    public function getMinuteComponents(): array;

    /**
     * @return IntervalComponentInterface[]
     */
    public function getHourComponents(): array;

    /**
     * @return IntervalComponentInterface[]
     */
    public function getDayComponents(): array;

    /**
     * @return IntervalComponentInterface[]
     */
    public function getMonthComponents(): array;

    /**
     * @return IntervalComponentInterface[]
     */
    public function getWeekdayComponents(): array;

    /**
     * @return IntervalComponentInterface[]
     */
    public function getYearComponents(): array;

    /**
     * Gets all available minutes in an hour
     *
     * @return int[]
     */
    public function getMinuteItems();

    /**
     * Gets all available hours in a day
     *
     * @return int[]
     */
    public function getHourItems();

    /**
     * Gets all available days in a month
     *
     * @return int[]
     */
    public function getDayItems();

    /**
     * Gets all available month in a year
     *
     * @return int[]
     */
    public function getMonthItems();

    /**
     * Gets all available weekdays
     *
     * @return int[]
     */
    public function getWeekdayItems();

    /**
     * Gets all available years
     *
     * @return int[]
     */
    public function getYearItems();
}
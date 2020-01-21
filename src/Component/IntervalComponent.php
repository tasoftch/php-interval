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

namespace TASoft\Util\Interval\Component;


class IntervalComponent implements IntervalComponentInterface
{
    /** @var int|null */
    private $minimum;
    /** @var int|null */
    private $maximum;
    /** @var int|null */
    private $interval;

    /**
     * ScheduleStringComponent constructor.
     * @param int|null $minimum
     * @param int|null $maximum
     * @param int|null $interval
     */
    public function __construct(?int $minimum, int $interval = NULL, int $maximum = NULL)
    {
        $this->minimum = $minimum;
        $this->maximum = $maximum;
        $this->interval = $interval;
    }

    public function hasRange(): bool {
        return $this->maximum !== NULL;
    }

    public function hasInterval(): bool {
        return $this->interval !== NULL;
    }

    /**
     * @return int|null
     */
    public function getMinimum(): ?int
    {
        return $this->minimum;
    }

    /**
     * @return int|null
     */
    public function getMaximum(): ?int
    {
        return $this->maximum;
    }

    /**
     * @return int|null
     */
    public function getInterval(): ?int
    {
        return $this->interval;
    }

    public function stringify(int $minimum = NULL, int $maximum = NULL, array $names = []): string {
        if($this->interval == 1 && $this->minimum == $minimum && $this->maximum == $maximum)
            return "*";

        $string = $this->getMinimum();
        if($this->hasRange()) {
            if($this->minimum == $minimum && $this->maximum == $maximum)
                $string = "*";
            else {
                $min = $this->getMinimum();
                $max = $this->getMaximum();

                if($names && ($idx = array_search($min, $names)) !== NULL)
                    $min = $idx;
                if($names && ($idx = array_search($max, $names)) !== NULL)
                    $max = $idx;

                $string = sprintf("%s-%s", $min, $max);
            }
        }

        if($this->hasInterval() && $this->interval>1) {
            return "$string/$this->interval";
        }
        return $string;
    }
}
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

namespace TASoft\Util\Interval\Exception;


class ScheduleIntervalMatchException extends ScheduleIntervalStringParseException
{
    /** @var array|null */
    private $expectedValues;
    /** @var mixed|null */
    private $receivedValue;

    /**
     * @return array|null
     */
    public function getExpectedValues(): ?array
    {
        return $this->expectedValues;
    }

    /**
     * @param array|null $expectedValues
     */
    public function setExpectedValues(?array $expectedValues): void
    {
        $this->expectedValues = $expectedValues;
    }

    /**
     * @return mixed|null
     */
    public function getReceivedValue()
    {
        return $this->receivedValue;
    }

    /**
     * @param mixed|null $receivedValue
     */
    public function setReceivedValue($receivedValue): void
    {
        $this->receivedValue = $receivedValue;
    }
}
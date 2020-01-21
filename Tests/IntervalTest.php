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

/**
 * IntervalTest.php
 * php-interval
 *
 * Created on 2020-01-21 17:13 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Util\Interval\Interval;
use TASoft\Util\Interval\Parser\IntervalStringParser;

class IntervalTest extends TestCase
{
    public function testParser() {
        /** @var Interval $int */
        $int = IntervalStringParser::parse( "* * * * *" );
        $this->assertEquals("* * * * *", $int);

        $int = IntervalStringParser::parse( "0 1-8/2 * 4 *" );
        $this->assertEquals("0 1-8/2 * 4 *", $int);

        $this->assertEquals([0], $int->getMinuteItems());
        $this->assertEquals([1, 3, 5, 7], $int->getHourItems());
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8,9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31], $int->getDayItems());
        $this->assertEquals([4], $int->getMonthItems());
        $this->assertEquals([0, 1, 2, 3, 4, 5, 6], $int->getWeekdayItems());

        $int = IntervalStringParser::parse( "0 1-8/2 * 4-6 *" );
        $this->assertEquals("0 1-8/2 * APR-JUN *", $int->stringify(true));
    }

    /**
     * @dataProvider dataProviders
     */
    public function testScheduleString($scheduleString, $date, $expected) {
        $result = IntervalStringParser::parse ($scheduleString);
        $this->assertEquals($expected, $result->match( $date ));
    }

    public function dataProviders() {
        return [
            ["* * * * * 2010,2013,2016-2019", new DateTime("2019-01-01 15:34"), true],
            ["0,23,57 * * JUN-SEP *", new DateTime("2019-01-01 15:34"), false],
            ["2-25,34 * 2-19/5 1,3,7 MON,TUE,FRI", new DateTime("2019-01-07 15:34"), true],
        ];
    }

    public function testSchedulable() {
        /** @var Interval $intv1 */
        $intv1 = IntervalStringParser::parse("* * * * *");
        /** @var Interval $intv2 */
        $intv2 = IntervalStringParser::parse("*/5 * * * *");

        $this->assertTrue($intv1->hasIntersection($intv2));
        $this->assertFalse($intv2->hasIntersection($intv1));
    }

    public function testNextDate() {
        $intv = IntervalStringParser::parse("* * * * *");
        $date = new DateTime("2019-11-30 12:37:33");
        $date = $intv->next($date);
        $this->assertEquals("2019-11-30 12:38", $date->format("Y-m-d G:i"));

        $intv = IntervalStringParser::parse("0 * * * *");
        $date = new DateTime("2019-11-30 12:37:33");
        $date = $intv->next($date);
        $this->assertEquals("2019-11-30 13:00", $date->format("Y-m-d G:i"));

        $intv = IntervalStringParser::parse("0 0 * * *");
        $date = new DateTime("2019-11-30 12:37:33");
        $date = $intv->next($date);
        $this->assertEquals("2019-12-01 0:00", $date->format("Y-m-d G:i"));

        $intv = IntervalStringParser::parse("0 0 4 * *");
        $date = new DateTime("2019-11-30 12:37:33");
        $date = $intv->next($date);
        $this->assertEquals("2019-12-04 0:00", $date->format("Y-m-d G:i"));

        $intv = IntervalStringParser::parse("0 0 2 APR-AUG *");
        $date = new DateTime("2019-11-30 12:37:33");
        $date = $intv->next($date);
        $this->assertEquals("2020-04-02 0:00", $date->format("Y-m-d G:i"));

        $intv = IntervalStringParser::parse("0 0 2 APR-AUG MON-WED");
        $date = new DateTime("2019-11-30 12:37:33");
        $date = $intv->next($date);
        $this->assertEquals("Tue 2020-06-02 0:00", $date->format("D Y-m-d G:i"));
    }
}
